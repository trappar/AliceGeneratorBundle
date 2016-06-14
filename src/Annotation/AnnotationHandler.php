<?php

namespace Trappar\AliceGeneratorBundle\Annotation;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Trappar\AliceGeneratorBundle\Faker\ProviderHelper;
use Trappar\AliceGeneratorBundle\FixtureGenerator;

class AnnotationHandler implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;
    /**
     * @var Reader
     */
    protected $reader;

    /**
     * @inheritdoc
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->reader    = $this->container->get('annotation_reader');
    }

    public function handlePropertyAnnotations(\ReflectionProperty $property, $class, $value, $object)
    {
        $annotations = $this->reader->getPropertyAnnotations($property);
        $annotations = array_values(array_filter($annotations, function ($annotation) {
            return is_a($annotation, FixtureAnnotationInterface::class);
        }));

        $valueModified = false;

        if (count($annotations) > 1) {
            throw new AnnotationException(sprintf(
                '%s - may not have more than one of @Faker, @Data, or @Ignore annotations on a single property.',
                $this->createContext('Annotations declared on', $property, $class)
            ));
        } elseif (count($annotations) == 1) {
            $annotation    = $annotations[0];
            $valueModified = true;

            if (is_a($annotation, Data::class)) {
                $value = $this->handleDataAnnotation($annotation, $property, $class);
            } elseif (is_a($annotation, Faker::class)) {
                $value = $this->handleFakerAnnotation($annotation, $property, $class, $value, $object);
            } elseif (is_a($annotation, Ignore::class)) {
                $value = FixtureGenerator::SKIP_VALUE;
            }
        }

        return [$valueModified, $value];
    }

    public function createProviderFromMethod(\ReflectionMethod $method, $arguments)
    {
        $class = $method->getDeclaringClass()->getName();
        /** @var Faker $annotation */
        $annotation = $this->reader->getMethodAnnotation($method, Faker::class);

        if (!$annotation) {
            throw new AnnotationException(sprintf(
                'Method %s of class "%s" - Must have @Faker annotation when returning array.',
                $method->getName(),
                $class
            ));
        } elseif (count(array_filter([$annotation->arguments, $annotation->class, $annotation->service, $annotation->valueAsArgs], function ($item) {
                return $item;
            })) > 0
        ) {
            throw new AnnotationException(sprintf(
                '@Faker annotation on method %s of class "%s" - May not have "arguments", "class", "service", or "valueAsArgs" attributes.',
                $method->getName(),
                $class
            ));
        }

        return ProviderHelper::generate($annotation->name, $arguments);
    }

    protected function handleFakerAnnotation(Faker $annotation, \ReflectionProperty $property, $class, $value, $object)
    {
        $context = $this->createContext('@Faker declared on', $property, $class);

        // Only allow one of arguments, class, or service
        if (count(array_filter([$annotation->arguments, $annotation->class, $annotation->service, $annotation->valueAsArgs], function ($item) {
                return $item;
            })) > 1
        ) {
            throw AnnotationException::typeError(sprintf(
                '%s - Only one of "valueAsArgs", "arguments", "class", or "service" can be declared.',
                $context
            ));
        }

        $userClass = null;
        $method = 'toFixture';

        if ($annotation->valueAsArgs) {
            $arguments = [$value];
        } elseif ($annotation->arguments) {
            // Due to the way annotations are parsed, this will always be an array. No type checking required.
            $arguments = $annotation->arguments;
        } elseif ($annotation->class) {
            $callParts = explode('::', $annotation->class);

            $method = (isset($userClass[1])) ? $userClass[1] : $method;
            $userClass = $callParts[0];

            if (!class_exists($userClass)) {
                // See if the class exists in the same namespace as the object whose property we're handling
                $namespace = $property->getDeclaringClass()->getNamespaceName();
                $userClass    = $namespace . '\\' . $userClass;

                if (!class_exists($userClass)) {
                    throw AnnotationException::typeError(sprintf(
                        '%s - Attribute "class" of must be a valid class.',
                        $context
                    ));
                }
            }
            if (!method_exists($userClass, $method)) {
                throw AnnotationException::typeError(sprintf(
                    '%s - Attribute "class" (%s) must contain a "%s" method.',
                    $context,
                    $userClass,
                    $method
                ));
            }

            $arguments = ProviderHelper::call($annotation->class, $method, [$value, $object]);
        } elseif ($annotation->service) {
            if (!$this->container->has($annotation->service)) {
                throw AnnotationException::typeError(sprintf(
                    '%s - Attribute "service" must be a valid service.',
                    $context
                ));
            }

            $service = $this->container->get($annotation->service);
            $userClass = get_class($service);

            if (!method_exists($service, 'toFixture')) {
                throw AnnotationException::typeError(sprintf(
                    '%s - Service "%s" must contain a toFixture method.',
                    $context,
                    $annotation->service
                ));
            }

            $arguments = ProviderHelper::call($service, $method, [$value, $object]);
        } else {
            $arguments = [];
        }

        if (!is_array($arguments)) {
            // This can only happen with class/service
            throw AnnotationException::typeError(sprintf(
                '%s - Called "%s" "%s" method and got %s instead of expected array.',
                $context,
                $userClass,
                $method,
                gettype($arguments)
            ));
        }

        return ProviderHelper::generate($annotation->name, $arguments);
    }

    protected function handleDataAnnotation(Data $annotation, \ReflectionProperty $property, $class)
    {
        if (!$annotation->value) {
            throw AnnotationException::typeError(sprintf(
                '%s - Attribute "value" must be declared.',
                $this->createContext('@Data declared on', $property, $class)
            ));
        }

        return $annotation->value;
    }

    protected function createContext($prefix, \ReflectionProperty $property, $class)
    {
        return sprintf(
            '%s property %s of class "%s"',
            $prefix,
            $property->getName(),
            $class
        );
    }
}
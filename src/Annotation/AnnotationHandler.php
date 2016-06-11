<?php

namespace Trappar\AliceGeneratorBundle\Annotation;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
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

    public function handleProperty(\ReflectionProperty $property, $class, $value)
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
                $value = $this->handleDataAnnotation($annotation, $property, $class, $value);
            } elseif (is_a($annotation, Faker::class)) {
                $value = $this->handleFakerAnnotation($annotation, $property, $class, $value);
            } elseif (is_a($annotation, Ignore::class)) {
                $value = FixtureGenerator::SKIPVALUE;
            }
        }

        return [$valueModified, $value];
    }

    public function handleFakerAnnotation(Faker $annotation, \ReflectionProperty $property, $class, $value)
    {
        $context = $this->createContext('@Faker declared on', $property, $class);

        if (!$annotation->name) {
            throw AnnotationException::typeError(sprintf(
                '%s - Attribute "name" must be declared.',
                $context
            ));
        }

        // Only allow one of arguments, class, or service
        if (count(array_filter([$annotation->arguments, $annotation->class, $annotation->service], function ($item) {
                return $item;
            })) > 1
        ) {
            throw AnnotationException::typeError(sprintf(
                '%s - Only one of "arguments", "class", or "service" can be declared.',
                $context
            ));
        }

        if ($annotation->arguments) {
            $arguments = $annotation->arguments;

            if (!is_array($annotation->arguments)) {
                throw AnnotationException::typeError(sprintf(
                    '%s - Attribute "arguments" must be an array.',
                    $context
                ));
            }
        } elseif ($annotation->class) {
            $callParts = explode('::', $annotation->class);

            $method    = (isset($userClass[1])) ? $userClass[1] : 'toFixture';
            $userClass = $callParts[0];

            if (!class_exists($annotation->class)) {
                throw AnnotationException::typeError(sprintf(
                    '%s - Attribute "class" of must be a valid class.',
                    $context
                ));
            }
            if (!method_exists($userClass, $method)) {
                throw AnnotationException::typeError(sprintf(
                    '%s - Attribute "class" (%s) must contain a "%s" method.',
                    $context,
                    $userClass,
                    $method
                ));
            }

            $arguments = call_user_func([$annotation->class, 'toFixture'], $value);
        } elseif ($annotation->service) {
            if (!$this->container->has($annotation->service)) {
                throw AnnotationException::typeError(sprintf(
                    '%s - Attribute "service" must be a valid service.',
                    $context
                ));
            }
            $service = $this->container->get($annotation->service);
            if (!method_exists($service, 'toFixture')) {
                throw AnnotationException::typeError(sprintf(
                    '%s - Service "%s" must contain a toFixture method.',
                    $context,
                    $annotation->service
                ));
            }

            $arguments = $service->toFixture($value);
        } else {
            $arguments = [];
        }

        $arguments = array_map(function ($item) use ($value) {
            if (is_string($item)) {
                return '"' . $item . '"';
            } elseif (is_bool($item)) {
                return ($item) ? 'true' : 'false';
            }

            return $item;
        }, $arguments);

        $arguments = implode($arguments, ', ');

        return "<{$annotation->name}($arguments)>";
    }

    public function handleDataAnnotation(Data $annotation, \ReflectionProperty $property, $class)
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
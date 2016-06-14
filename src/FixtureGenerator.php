<?php

namespace Trappar\AliceGeneratorBundle;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Symfony\Component\Yaml\Yaml;
use Trappar\AliceGeneratorBundle\Annotation\AnnotationHandler;
use Trappar\AliceGeneratorBundle\DataStorage\EntityCache;
use Trappar\AliceGeneratorBundle\Faker\ProviderHelper;

class FixtureGenerator
{
    const SKIP_VALUE = 'FIXTURE_GENERATOR_SKIP_VALUE';

    private $providers = [];

    /**
     * @var ClassMetadataFactory
     */
    private $metadataFactory;

    /**
     * @var array
     */
    private $resultCache;

    /**
     * @var int
     */
    private $recursionDepth;

    /**
     * @var FixtureGenerationContext
     */
    private $fixtureGenerationContext;

    /**
     * @var EntityCache
     */
    private $entityCache;

    public function __construct(EntityManager $em, AnnotationHandler $handler)
    {
        $this->metadataFactory   = $em->getMetadataFactory();
        $this->annotationHandler = $handler;
    }

    public function generateYaml($value, $fixtureGenerationContext = null)
    {
        if (!$fixtureGenerationContext) {
            $fixtureGenerationContext = new FixtureGenerationContext();
        }
        $this->fixtureGenerationContext = $fixtureGenerationContext;

        // Reset the result and object caches
        $this->resultCache = [];
        $this->entityCache = new EntityCache();

        $this->handleUnknownType($value);

        return Yaml::dump($this->resultCache, 3);
    }

    public function addProvider($provider)
    {
        if (method_exists($provider, 'toFixture')) {
            $this->providers[] = $provider;
        } else {
            throw new \InvalidArgumentException(sprintf(
                'Fixture Generator Provider %s - "toFixture" must exist.',
                get_class($provider)
            ));
        }
    }

    protected function handleUnknownType($value, $context = null, $contextPropName = null)
    {
        if (is_object($value) && !is_a($value, Collection::class)) {
            return $this->handleObject($value, $context, $contextPropName);
        }

        if (is_array($value) || is_a($value, Collection::class)) {
            return $this->handleArray($value, $context, $contextPropName);
        }

        return $value;
    }

    protected function handleObject($object, $context, $contextPropName)
    {
        $object = $this->applyProviders($object, $context, $contextPropName);

        if (is_object($object) && $this->isObjectMapped($object)) {
            if (!$this->fixtureGenerationContext->getEntityConstraints()->checkValid($object)) {
                return self::SKIP_VALUE;
            }

            $result          = $this->entityCache->find($object);
            $referencePrefix = $this->fixtureGenerationContext->getReferenceNamer()->createPrefix($object);

            switch ($result) {
                case EntityCache::OBJECT_NOT_FOUND:
                    if ($this->recursionDepth <= $this->fixtureGenerationContext->getMaximumRecursion()) {
                        $key       = $this->entityCache->add($object);
                        $reference = $referencePrefix . $key;

                        $objectAdded = $this->handleEntity($object, $reference);

                        if ($objectAdded) {
                            return '@' . $reference;
                        } else {
                            $this->entityCache->skip($object);

                            return self::SKIP_VALUE;
                        }
                    }
                    break;
                case EntityCache::OBJECT_SKIPPED:
                    return self::SKIP_VALUE;
                default:
                    return '@' . $referencePrefix . $result;
            }

            return self::SKIP_VALUE;
        }

        return $object;
    }

    protected function handleArray($array, $context, $contextPropName)
    {
        if (is_a($array, Collection::class)) {
            /** @var Collection $array */
            $array = $array->toArray();
        }

        foreach ($array as $key => &$item) {
            $item = $this->handleUnknownType($item, $context, $contextPropName);
            if ($item === self::SKIP_VALUE) {
                unset($array[$key]);
            }
        }

        if (!count($array)) {
            return self::SKIP_VALUE;
        }

        return $array;
    }

    protected function applyProviders($object, $context, $contextPropName)
    {
        $class = ClassUtils::getClass($object);

        foreach ($this->providers as $typeProvider) {
            $userClass        = get_class($typeProvider);
            $reflectionMethod = new \ReflectionMethod($userClass, 'toFixture');
            /** @var \ReflectionParameter[] $params */
            $params = $reflectionMethod->getParameters();

            if (count($params) < 1) {
                throw new \InvalidArgumentException(sprintf(
                    'Fixture Generator Provider %s - "toFixture" method must contain at least one argument.',
                    $userClass
                ));
            }

            if (!$params[0]->getClass()) {
                throw new \InvalidArgumentException(sprintf(
                    'Fixture Generator Provider %s - "toFixture" method\'s first argument must have an object type declaration.',
                    $userClass
                ));
            }

            if ($params[0]->getClass()->getName() == $class) {
                $returnValue = ProviderHelper::call($typeProvider, 'toFixture', [$object, $context, $contextPropName]);

                if (is_array($returnValue)) {
                    return $this->annotationHandler->createProviderFromMethod($reflectionMethod, $returnValue);
                } else {
                    return $returnValue;
                }
            }
        }

        return $object;
    }

    /**
     * @param       $object
     * @param       $reference
     * @return bool if the object was added to the object cache
     * @throws \Doctrine\ORM\Mapping\MappingException
     * @throws \Exception
     */
    protected function handleEntity($object, $reference)
    {
        $class = ClassUtils::getClass($object);

        // Force proxy objects to load data
        if (method_exists($object, '__load')) {
            $object->__load();
        }

        // Create a new instance of this class to check values against
        $newObject = new $class();

        /**
         * @var ClassMetadata         $classMetadata
         * @var \ReflectionProperty[] $reflectionProperties
         */
        $classMetadata        = $this->metadataFactory->getMetadataFor($class);
        $reflectionProperties = $classMetadata->getReflectionProperties();
        $identifiers          = $classMetadata->getIdentifier();
        $associations         = $classMetadata->getAssociationMappings();

        $saveValues = [];
        $this->recursionDepth++;

        foreach ($reflectionProperties as $propName => $property) {
            // Skip ID properties
            if (in_array($propName, $identifiers)) {
                continue;
            }

            $value        = $property->getValue($object);
            $initialValue = $property->getValue($newObject);

            list($wasThereAnnotation, $value) = $this->annotationHandler
                ->handlePropertyAnnotations($property, $class, $value, $object);

            if (!$wasThereAnnotation) {
                // Avoid setting unnecessary data
                if (is_null($value) || is_bool($value) || is_object($value)) {
                    if ($value === $initialValue) {
                        continue;
                    }
                } else {
                    if ($value == $initialValue) {
                        continue;
                    }
                }

                $value = $this->handleUnknownType($value, $object, $propName);

                // No need to make references for non-owning side associations
                if (isset($associations[$propName])) {
                    if (!$classMetadata->getAssociationMapping($propName)['isOwningSide']) {
                        continue;
                    }
                }
            }

            if ($value === self::SKIP_VALUE) {
                continue;
            }

            $saveValues[$propName] = $value;
        }

        $this->recursionDepth--;

        if (!count($saveValues)) {
            return false;
        } else {
            $this->resultCache[$class][$reference] = $saveValues;

            return true;
        }
    }

    protected function isObjectMapped($object)
    {
        try {
            $this->metadataFactory->getMetadataFor(get_class($object));

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
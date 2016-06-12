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

class FixtureGenerator
{
    const SKIPVALUE = 'FIXTURE_GENERATOR_SKIP_VALUE';

    private $typeProviders = [];

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

    public function addTypeProvider($provider)
    {
        $this->typeProviders[] = $provider;
    }

    private function handleUnknownType($value)
    {
        if (is_object($value) && !is_a($value, Collection::class)) {
            return $this->handleObject($value);
        }

        if (is_array($value) || is_a($value, Collection::class)) {
            return $this->handleArray($value);
        }

        return $value;
    }

    protected function handleObject($object)
    {
        if ($this->isObjectMapped($object)) {
            if (!$this->fixtureGenerationContext->getEntityConstraints()->checkValid($object)) {
                return self::SKIPVALUE;
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

                            return self::SKIPVALUE;
                        }
                    }
                    break;
                case EntityCache::OBJECT_SKIPPED:
                    return self::SKIPVALUE;
                default:
                    return '@' . $referencePrefix . $result;
            }

            return self::SKIPVALUE;
        } else {
            return $this->applyTypeProviders($object);
        }
    }

    protected function handleArray($array)
    {
        if (is_a($array, Collection::class)) {
            /** @var Collection $array */
            $array = $array->toArray();
        }

        foreach ($array as $key => &$item) {
            $item = $this->handleUnknownType($item);
            if ($item === self::SKIPVALUE) {
                unset($array[$key]);
            }
        }

        if (!count($array)) {
            return self::SKIPVALUE;
        }

        return $array;
    }

    protected function applyTypeProviders($object)
    {
        $class = ClassUtils::getClass($object);

        foreach ($this->typeProviders as $typeProvider) {
            if (method_exists($typeProvider, 'toFixture')) {
                $userClass        = get_class($typeProvider);
                $reflectionMethod = new \ReflectionMethod($userClass, 'toFixture');
                /** @var \ReflectionParameter[] $params */
                $params = $reflectionMethod->getParameters();

                if (count($params) != 1) {
                    throw new \InvalidArgumentException(sprintf(
                        'Fixture Generator Provider %s - "toFixture" method must contain exactly one argument.',
                        get_class($typeProvider)
                    ));
                }

                if ($params[0]->getClass()->getName() == $class) {
                    $returnValue = $typeProvider->toFixture($object);

                    if (is_array($returnValue)) {
                        return $this->annotationHandler->getProviderFromMethod($reflectionMethod, $userClass, $returnValue);
                    } else {
                        return $returnValue;
                    }
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

            list($wasThereAnnotation, $value) = $this->annotationHandler->handlePropertyAnnotations($property, $class, $value);

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

                $value = $this->handleUnknownType($value);

                // No need to make references for non-owning side associations
                if (isset($associations[$propName])) {
                    if (!$classMetadata->getAssociationMapping($propName)['isOwningSide']) {
                        continue;
                    }
                }
            }

            if ($value === self::SKIPVALUE) {
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
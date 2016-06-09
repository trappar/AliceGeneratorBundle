<?php

namespace Trappar\AliceGeneratorBundle;

use Trappar\AliceGeneratorBundle\DataStorage\EntityConstraints;
use Trappar\AliceGeneratorBundle\ReferenceNamer\ClassNamer;
use Trappar\AliceGeneratorBundle\ReferenceNamer\ReferenceNamerInterface;

class FixtureGenerationContext
{
    /**
     * @var int
     */
    protected $maximumRecursion = 5;

    protected $entityConstraints;

    /**
     * @var ReferenceNamerInterface
     */
    protected $referenceNamer;

    public static function create()
    {
        return new static();
    }

    public function __construct()
    {
        $this->referenceNamer    = new ClassNamer();
        $this->entityConstraints = new EntityConstraints();
    }

    /**
     * @return int
     */
    public function getMaximumRecursion()
    {
        return $this->maximumRecursion;
    }

    /**
     * @param int $max
     * @return FixtureGenerationContext
     */
    public function setMaximumRecursion($max)
    {
        $this->maximumRecursion = $max;

        return $this;
    }

    public function getEntityConstraints()
    {
        return $this->entityConstraints;
    }

    /**
     * @param $entity
     * @return FixtureGenerationContext
     */
    public function addEntityConstraint($entity)
    {
        $this->getEntityConstraints()->add($entity);

        return $this;
    }

    /**
     * @return ReferenceNamerInterface
     */
    public function getReferenceNamer()
    {
        return $this->referenceNamer;
    }

    /**
     * @param ReferenceNamerInterface $referenceNamer
     * @return FixtureGenerationContext
     */
    public function setReferenceNamer(ReferenceNamerInterface $referenceNamer)
    {
        $this->referenceNamer = $referenceNamer;

        return $this;
    }
}
<?php

namespace Trappar\AliceGeneratorBundle\DataStorage;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Util\ClassUtils;

abstract class AbstractSubdividedCollection
{
    private $stores = [];

    /**
     * @param $object
     * @return ArrayCollection
     */
    public function getStore($object)
    {
        $subdivision = $this->determineSubdivision($object);

        if (!isset($this->stores[$subdivision])) {
            $this->stores[$subdivision] = $this->getBackingStore();
        }

        return $this->stores[$subdivision];
    }

    protected function determineSubdivision($object)
    {
        return ClassUtils::getClass($object);
    }

    protected function getBackingStore()
    {
        return new ArrayCollection;
    }
}
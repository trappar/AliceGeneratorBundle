<?php

namespace Trappar\AliceGeneratorBundle\DataStorage;

class EntityConstraints extends AbstractSubdividedCollection
{
    public function add($object)
    {
        $this->getStore($object)->add($object);
    }
    
    public function checkValid($object)
    {
        $store = $this->getStore($object);
        if ($store->count()) {
            return $store->contains($object);
        }
        
        return true;
    }
}
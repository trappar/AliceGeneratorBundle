<?php

namespace Trappar\AliceGeneratorBundle\ReferenceNamer;

interface ReferenceNamerInterface
{
    /**
     * @param $object
     * @return string
     */
    public function createPrefix($object);
}
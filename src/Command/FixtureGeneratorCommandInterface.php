<?php

namespace Trappar\AliceGeneratorBundle\Command;

use Symfony\Component\Console\Input\InputInterface;

interface FixtureGeneratorCommandInterface
{
    /**
     * Return the entities you want to have converted to fixtures.
     * 
     * @param InputInterface $input
     * @return mixed Can be a single entity, an array of entities, or a Collection of entities.
     */
    public function getEntities(InputInterface $input);

    /**
     * @return string Absolute path to the output location for the generated file.
     */
    public function getOutputLocation();
}
<?php

namespace Trappar\AliceGeneratorBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface FixtureGeneratorCommandInterface
{
    /**
     * Return the entities you want to have converted to fixtures. You can use the InputInterface to pass in information
     * from the command line which you can then use to determine which entities to return. You can also use the OutputInterface
     * to return feedback or errors to the command user.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return mixed Can be a single entity, an array of entities, or a Collection of entities.
     */
    public function getEntities(InputInterface $input, OutputInterface $output);

    /**
     * @return string Absolute path to the output location for the generated file.
     */
    public function getOutputLocation();
}
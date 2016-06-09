<?php

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        return [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Hautelook\AliceBundle\HautelookAliceBundle(),
            new Trappar\AliceGeneratorBundle\TrapparAliceGeneratorBundle(),
            new Trappar\AliceGeneratorBundle\Tests\SymfonyApp\TestBundle\TestBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/config/config.yml');
    }
}

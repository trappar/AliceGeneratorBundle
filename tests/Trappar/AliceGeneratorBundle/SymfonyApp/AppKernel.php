<?php

namespace Trappar\AliceGeneratorBundle\Tests\SymfonyApp;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;
use Trappar\AliceGeneratorBundle\Tests\SymfonyApp\TestBundle\TestBundle;
use Trappar\AliceGeneratorBundle\TrapparAliceGeneratorBundle;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        return [
            new FrameworkBundle(),
            new DoctrineBundle(),
            new TrapparAliceGeneratorBundle(),
            new TestBundle()
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/config/config.yml');
    }
}

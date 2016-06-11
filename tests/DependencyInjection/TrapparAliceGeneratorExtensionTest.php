<?php

namespace Trappar\AliceGeneratorBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Trappar\AliceGeneratorBundle\DependencyInjection\TrapparAliceGeneratorExtension;


class TrapparAliceGeneratorExtensionTest extends TestCase
{
    /**
     * @cover ::load
     */
    public function testLoad()//(array $configs, ContainerBuilder $container)
    {
        $extension = new TrapparAliceGeneratorExtension();
        $this->assertInstanceOf(ExtensionInterface::class, $extension);
        
        $containerBuilder = $this->getMockBuilder(ContainerBuilder::class)
            ->setMethods(['setDefinition'])
            ->getMock();

        $containerBuilder
            ->expects($this->any())
            ->method('setDefinition')
            ->with($this->logicalOr(
                'trappar_alice_generator.fixture_generator',
                'trappar_alice_generator.annotation.handler',
                'trappar_alice_generator.command.fixture_generator'
            ), $this->isInstanceOf(Definition::class));
        
        $extension->load([], $containerBuilder);
    }
}

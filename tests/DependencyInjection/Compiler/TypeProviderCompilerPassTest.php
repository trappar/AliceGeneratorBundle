<?php

namespace Trappar\AliceGeneratorBundle\Tests\DependencyInjection\Compiler;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Trappar\AliceGeneratorBundle\DependencyInjection\Compiler\TypeProviderCompilerPass;

/**
 * @coversDefaultClass Trappar\AliceGeneratorBundle\DependencyInjection\Compiler\ProviderCompilerPass
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class TypeProviderCompilerPassTest extends TestCase
{
    public function testProcess()
    {
        $providerPass = new TypeProviderCompilerPass();

        $this->assertInstanceOf(CompilerPassInterface::class, $providerPass);

        $definition = $this->getMockBuilder(Definition::class)->getMock();
        
        $containerBuilder = $this->getMockBuilder(ContainerBuilder::class)
            ->setMethods(['getDefinition', 'findTaggedServiceIds'])
            ->getMock();
        
        $containerBuilder->method('getDefinition')->willReturn($definition);
        $containerBuilder->method('findTaggedServiceIds')->willReturn(['foo', 'bar']);

        $containerBuilder->expects($this->once())
            ->method('findTaggedServiceIds');

        $definition->expects($this->exactly(2))
            ->method('addMethodCall');

        $providerPass->process($containerBuilder);
    }
}

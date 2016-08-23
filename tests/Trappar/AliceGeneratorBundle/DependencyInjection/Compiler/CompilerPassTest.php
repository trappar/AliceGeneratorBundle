<?php

namespace Trappar\AliceGeneratorBundle\Tests\DependencyInjection\Compiler;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Trappar\AliceGeneratorBundle\DependencyInjection\Compiler\CompilerPass;

class CompilerPassTest extends TestCase
{
    public function testProcess()
    {
        $providerPass = new CompilerPass();

        $this->assertInstanceOf(CompilerPassInterface::class, $providerPass);

        $resolverDefinition = $this->getMockBuilder(Definition::class)->getMock();
        $resolverDefinition->expects($this->exactly(2))->method('addMethodCall');

        $handlerDefinition = clone $resolverDefinition;

        $containerBuilder = $this->getMockBuilder(ContainerBuilder::class)
            ->setMethods(['getDefinition', 'findTaggedServiceIds'])
            ->getMock();

        $containerBuilder->method('getDefinition')
            ->will($this->onConsecutiveCalls($resolverDefinition, $handlerDefinition));
        $containerBuilder->method('findTaggedServiceIds')->willReturn(['foo', 'bar']);

        $containerBuilder->expects($this->exactly(2))
            ->method('findTaggedServiceIds');

        /** @var ContainerBuilder $containerBuilder */
        $providerPass->process($containerBuilder);
    }
}
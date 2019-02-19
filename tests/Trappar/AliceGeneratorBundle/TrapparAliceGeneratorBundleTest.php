<?php

namespace Trappar\AliceGeneratorBundle\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Trappar\AliceGeneratorBundle\DependencyInjection\Compiler\CompilerPass;
use Trappar\AliceGeneratorBundle\TrapparAliceGeneratorBundle;

class TrapparAliceGeneratorBundleTest extends TestCase
{
    public function testBuild()
    {
        $bundle = new TrapparAliceGeneratorBundle();

        $this->assertInstanceOf(Bundle::class, $bundle);

        $containerBuilder = $this->getMockBuilder(ContainerBuilder::class)
            ->setMethods(['addCompilerPass'])
            ->getMock();

        $containerBuilder->expects($this->once())
            ->method('addCompilerPass')
            ->with(new CompilerPass());

        $bundle->build($containerBuilder);
    }
}
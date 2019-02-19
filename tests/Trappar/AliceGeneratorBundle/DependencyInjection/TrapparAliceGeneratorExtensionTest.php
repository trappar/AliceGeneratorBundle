<?php

namespace Trappar\AliceGeneratorBundle\Tests\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Trappar\AliceGenerator\Exception\RuntimeException;
use Trappar\AliceGenerator\ValueVisitor;
use Trappar\AliceGenerator\YamlWriter;
use Trappar\AliceGeneratorBundle\DependencyInjection\TrapparAliceGeneratorExtension;

class TrapparAliceGeneratorExtensionTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions()
    {
        return array(
            new TrapparAliceGeneratorExtension()
        );
    }

    public function testAutoDetectionDisabled()
    {
        $this->setBundles();
        $this->load([
            'metadata' => [
                'auto_detection' => false
            ]
        ]);

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'trappar_alice_generator.metadata.file_locator',
            0,
            []
        );
    }

    public function testWithCustomDirectory()
    {
        $this->setBundles();
        $this->load([
            'metadata' => [
                'auto_detection' => false,
                'directories' => [
                    'myname' => [
                        'namespace_prefix' => 'some_prefix',
                        'path'             => '@TestBundle'
                    ]
                ]
            ]
        ]);

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'trappar_alice_generator.metadata.file_locator',
            0,
            ['some_prefix' => realpath(__DIR__ . '/../SymfonyApp/TestBundle')]
        );
    }

    public function testAutoDetectionEnabled()
    {
        $this->setBundles();
        $this->load();

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'trappar_alice_generator.metadata.file_locator',
            0
        );
    }

    public function testWithInvalidBundle()
    {
        $this->expectException(RuntimeException::class);

        $this->setBundles();
        $this->load([
            'metadata' => [
                'directories' => [
                    'myname' => [
                        'namespace_prefix' => 'some_prefix',
                        'path'             => '@Blah'
                    ]
                ]
            ]
        ]);
    }

    public function testWithCustomYamlOptions()
    {
        $this->setBundles();
        $this->load([
            'yaml' => [
                'indent' => 1
            ]
        ]);

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            YamlWriter::class,
            0,
            3
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            YamlWriter::class,
            1,
            1
        );
    }

    public function testWithDisabledStrictTypeChecking()
    {
        $this->setBundles();
        $this->load();
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            ValueVisitor::class,
            5,
            true
        );

        $this->load(['strictTypeChecking' => false]);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            ValueVisitor::class,
            5,
            false
        );
    }

    private function setBundles()
    {
        $this->setParameter('kernel.bundles', [
            'TestBundle' => 'Trappar\AliceGeneratorBundle\Tests\SymfonyApp\TestBundle\TestBundle'
        ]);
    }
}

<?php

namespace Trappar\AliceGeneratorBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Trappar\AliceGenerator\Exception\RuntimeException;
use Trappar\AliceGeneratorBundle\DependencyInjection\TrapparAliceGeneratorExtension;

class TrapparAliceGeneratorExtensionTest extends TestCase
{
    public function testAutoDetectionDisabled()
    {
        $fileLocatorDefinition = $this->getDefinition();
        $fileLocatorDefinition->expects($this->once())->method('addArgument')->with([]);

        $this->runConfiguration(
            [
                'trappar_alice_generator' => [
                    'metadata' => [
                        'auto_detection' => false
                    ]
                ]
            ],
            $fileLocatorDefinition,
            $this->getDefinition()
        );
    }

    public function testWithCustomDirectory()
    {
        $fileLocatorDefinition = $this->getDefinition();
        $fileLocatorDefinition->expects($this->once())->method('addArgument')
            ->with($this->callback(function ($array) {
                return count($array) == 2 && $array['some_prefix'] == realpath(__DIR__ . '/../SymfonyApp/TestBundle');
            }));

        $this->runConfiguration(
            [
                'trappar_alice_generator' => [
                    'metadata' => [
                        'directories' => [
                            'myname' => [
                                'namespace_prefix' => 'some_prefix',
                                'path'             => '@TestBundle'
                            ]
                        ]
                    ]
                ]
            ],
            $fileLocatorDefinition,
            $this->getDefinition()
        );
    }

    public function testWithInvalidBundle()
    {
        $this->expectException(RuntimeException::class);

        $this->runConfiguration(
            [
                'trappar_alice_generator' => [
                    'metadata' => [
                        'directories' => [
                            'myname' => [
                                'namespace_prefix' => 'some_prefix',
                                'path'             => '@Blah'
                            ]
                        ]
                    ]
                ]
            ],
            $this->getDefinition(), $this->getDefinition()
        );
    }

    public function testWithCustomYamlOptions()
    {
        $yamlWriterDefinition = $this->getDefinition();
        $yamlWriterDefinition
            ->expects($this->exactly(2))
            ->method('addArgument')
            ->withConsecutive([3], [1]);

        $this->runConfiguration(
            [
                'trappar_alice_generator' => [
                    'yaml' => [
                        'indent' => 1
                    ]
                ]
            ],
            $this->getDefinition(),
            $yamlWriterDefinition
        );
    }

    private function runConfiguration($config, $fileLocatorDefinition, $yamlWriterDefinition)
    {
        $extension        = new TrapparAliceGeneratorExtension();
        $containerBuilder = $this->getContainerBuilderMock();

        $containerBuilder->method('getDefinition')
            ->will($this->returnValueMap([
                ['trappar_alice_generator.metadata.file_locator', $fileLocatorDefinition],
                ['trappar_alice_generator.yaml_writer', $yamlWriterDefinition]
            ]));

        $extension->load($config, $containerBuilder);
    }

    private function getContainerBuilderMock()
    {
        $containerBuilder = $this->createMock(ContainerBuilder::class);

        $containerBuilder
            ->expects($this->atLeastOnce())
            ->method('setDefinition');

        $containerBuilder->method('getParameter')->with('kernel.bundles')->willReturn([
            'TestBundle' => 'Trappar\AliceGeneratorBundle\Tests\SymfonyApp\TestBundle\TestBundle'
        ]);

        return $containerBuilder;
    }

    private function getDefinition()
    {
        $definition = $this->createMock(Definition::class);
        $definition->method('addArgument')->willReturnSelf();

        return $definition;
    }
}

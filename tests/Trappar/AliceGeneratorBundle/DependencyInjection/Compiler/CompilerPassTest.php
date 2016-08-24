<?php

namespace Trappar\AliceGeneratorBundle\Tests\DependencyInjection\Compiler;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Trappar\AliceGeneratorBundle\DependencyInjection\Compiler\CompilerPass;

class CompilerPassTest extends AbstractCompilerPassTestCase
{
    public function testProcess()
    {
        $this->setDefinition('trappar_alice_generator.metadata.resolver', new Definition());
        $this->setDefinition('trappar_alice_generator.object_handler_registry', new Definition());

        $testFakerResolver = new Definition();
        $testFakerResolver->addTag('trappar_alice_generator.faker_resolver');
        $this->setDefinition('custom_faker_resolver', $testFakerResolver);

        $testObjectHandler = new Definition();
        $testObjectHandler->addTag('trappar_alice_generator.object_handler');
        $this->setDefinition('custom_object_handler', $testObjectHandler);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'trappar_alice_generator.metadata.resolver',
            'addFakerResolvers',
            [[new Reference('custom_faker_resolver')]]
        );

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'trappar_alice_generator.object_handler_registry',
            'registerHandlers',
            [[new Reference('custom_object_handler')]]
        );
    }

    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new CompilerPass());
    }
}
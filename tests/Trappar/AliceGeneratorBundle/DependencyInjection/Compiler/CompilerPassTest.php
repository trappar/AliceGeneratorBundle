<?php

namespace Trappar\AliceGeneratorBundle\Tests\DependencyInjection\Compiler;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Trappar\AliceGenerator\Metadata\Resolver\MetadataResolver;
use Trappar\AliceGenerator\ObjectHandlerRegistry;
use Trappar\AliceGeneratorBundle\DependencyInjection\Compiler\CompilerPass;

class CompilerPassTest extends AbstractCompilerPassTestCase
{
    public function testProcess()
    {
        $this->setDefinition(MetadataResolver::class, new Definition());
        $this->setDefinition(ObjectHandlerRegistry::class, new Definition());

        $testFakerResolver = new Definition();
        $testFakerResolver->addTag('trappar_alice_generator.faker_resolver');
        $this->setDefinition('custom_faker_resolver', $testFakerResolver);

        $testObjectHandler = new Definition();
        $testObjectHandler->addTag('trappar_alice_generator.object_handler');
        $this->setDefinition('custom_object_handler', $testObjectHandler);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            MetadataResolver::class,
            'addFakerResolvers',
            [[new Reference('custom_faker_resolver')]]
        );

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            ObjectHandlerRegistry::class,
            'registerHandlers',
            [[new Reference('custom_object_handler')]]
        );
    }

    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new CompilerPass());
    }
}
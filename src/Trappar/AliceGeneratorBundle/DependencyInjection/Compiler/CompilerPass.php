<?php

namespace Trappar\AliceGeneratorBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class CompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        // Add Faker Resolvers to MetadataResolver
        $taggedServices = $container->findTaggedServiceIds('trappar_alice_generator.faker_resolver');
        $resolverDefinition = $container->getDefinition('trappar_alice_generator.metadata.resolver');
        $resolvers = [];
        foreach ($taggedServices as $resolverId => $tags) {
            $resolvers[] = new Reference($resolverId);
        }
        $resolverDefinition->addMethodCall('addFakerResolvers', [$resolvers]);

        // Add Object Handlers to ObjectHandlerRegistry
        $taggedServices = $container->findTaggedServiceIds('trappar_alice_generator.object_handler');
        $handlerDefinition = $container->getDefinition('trappar_alice_generator.object_handler_registry');
        $handlers = [];
        foreach ($taggedServices as $handlerId => $tags) {
            $handlers[] = new Reference($handlerId);
        }
        $handlerDefinition->addMethodCall('registerHandlers', [$handlers]);
    }
}

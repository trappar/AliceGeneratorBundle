<?php

namespace Trappar\AliceGeneratorBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Trappar\AliceGenerator\Metadata\Resolver\MetadataResolver;
use Trappar\AliceGenerator\ObjectHandlerRegistry;

final class CompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        // Add Faker Resolvers to MetadataResolver
        if ($container->hasDefinition(MetadataResolver::class)) {
            $taggedServices     = $container->findTaggedServiceIds('trappar_alice_generator.faker_resolver');
            $resolverDefinition = $container->getDefinition(MetadataResolver::class);
            $resolvers          = [];
            foreach ($taggedServices as $resolverId => $tags) {
                $resolvers[] = new Reference($resolverId);
            }
            $resolverDefinition->addMethodCall('addFakerResolvers', [$resolvers]);
        }

        // Add Object Handlers to ObjectHandlerRegistry
        if ($container->hasDefinition(ObjectHandlerRegistry::class)) {
            $taggedServices    = $container->findTaggedServiceIds('trappar_alice_generator.object_handler');
            $handlerDefinition = $container->getDefinition(ObjectHandlerRegistry::class);
            $handlers          = [];
            foreach ($taggedServices as $handlerId => $tags) {
                $handlers[] = new Reference($handlerId);
            }
            $handlerDefinition->addMethodCall('registerHandlers', [$handlers]);
        }
    }
}

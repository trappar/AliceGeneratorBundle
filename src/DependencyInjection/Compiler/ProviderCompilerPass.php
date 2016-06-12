<?php

namespace Trappar\AliceGeneratorBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Add registered Faker providers instances to the {@see Hautelook\AliceBundle\Faker\ProvidersChain}.
 */
final class ProviderCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('trappar_alice_generator.fixture_generator');

        $taggedServices = $container->findTaggedServiceIds('trappar_alice_generator.faker.provider');
        foreach ($taggedServices as $providerId => $tags) {
            $provider = new Reference($providerId);

            $definition->addMethodCall('addTypeProvider', [$provider]);
        }
    }
}

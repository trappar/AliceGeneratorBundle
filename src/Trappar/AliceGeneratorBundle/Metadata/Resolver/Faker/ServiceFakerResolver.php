<?php

namespace Trappar\AliceGeneratorBundle\Metadata\Resolver\Faker;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Trappar\AliceGenerator\DataStorage\ValueContext;
use Trappar\AliceGenerator\Exception\FakerResolverException;
use Trappar\AliceGenerator\Metadata\Resolver\Faker\AbstractFakerResolver;

class ServiceFakerResolver extends AbstractFakerResolver
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @inheritDoc
     */
    public function getType()
    {
        return 'service';
    }

    public function validate(ValueContext $valueContext)
    {
        list($serviceName, $method) = $this->getTarget($valueContext);

        if (!$this->container->has($serviceName)) {
            throw new FakerResolverException($valueContext, sprintf(
                'non-existent service given: "%s".',
                $serviceName
            ));
        }

        $service = $this->container->get($serviceName);
        if (!is_callable([$service, $method])) {
            throw new FakerResolverException($valueContext, sprintf(
                'service "%s" does not have a callable "%s" method',
                $serviceName,
                $method
            ));
        }
    }

    /**
     * @inheritDoc
     */
    public function handle(ValueContext $valueContext)
    {
        list($serviceName, $method) = $this->getTarget($valueContext);

        return call_user_func([$this->container->get($serviceName), $method], $valueContext);
    }

    private function getTarget(ValueContext $valueContext)
    {
        $args   = $valueContext->getMetadata()->fakerResolverArgs;
        $service = isset($args[0]) ? $args[0] : null;
        $method = isset($args[1]) ? $args[1] : 'toFixture';

        return [$service, $method];
    }
}
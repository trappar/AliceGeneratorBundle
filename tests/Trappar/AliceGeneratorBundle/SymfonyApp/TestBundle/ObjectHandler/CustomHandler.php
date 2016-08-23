<?php

namespace Trappar\AliceGeneratorBundle\Tests\SymfonyApp\TestBundle\ObjectHandler;

use Trappar\AliceGenerator\DataStorage\ValueContext;
use Trappar\AliceGenerator\ObjectHandler\ObjectHandlerInterface;

class CustomHandler implements ObjectHandlerInterface
{
    /**
     * @inheritDoc
     */
    public function handle(ValueContext $valueContext)
    {
    }
}
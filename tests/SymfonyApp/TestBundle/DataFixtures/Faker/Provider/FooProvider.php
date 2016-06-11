<?php

namespace Trappar\AliceGeneratorBundle\Tests\SymfonyApp\TestBundle\DataFixtures\Faker\Provider;

class FooProvider
{
    public static function toFixture($value)
    {
        return ['blah', 1 ,true];
    }
}
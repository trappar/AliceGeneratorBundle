<?php

namespace Trappar\AliceGeneratorBundle\Tests\SymfonyApp\TestBundle\DataFixtures\Faker\Provider;

use Trappar\AliceGeneratorBundle\Annotation as Fixture;

class NonArrayReturningProvider
{
    public static function toFixture()
    {
        return '<whoops()>';
    }
}
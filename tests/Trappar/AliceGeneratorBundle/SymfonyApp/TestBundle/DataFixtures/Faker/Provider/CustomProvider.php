<?php

namespace Trappar\AliceGeneratorBundle\Tests\SymfonyApp\TestBundle\DataFixtures\Faker\Provider;

use Trappar\AliceGenerator\Annotation as Fixture;

class CustomProvider
{
    /**
     * @param \stdClass $object
     * @return array
     * @Fixture\Faker("test")
     */
    public static function toFixture(\stdClass $object)
    {
        return [$object->value];
    }
}
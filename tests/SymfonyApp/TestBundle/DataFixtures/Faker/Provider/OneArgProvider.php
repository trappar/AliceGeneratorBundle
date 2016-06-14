<?php

namespace Trappar\AliceGeneratorBundle\Tests\SymfonyApp\TestBundle\DataFixtures\Faker\Provider;

use Trappar\AliceGeneratorBundle\Annotation as Fixture;

class OneArgProvider
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
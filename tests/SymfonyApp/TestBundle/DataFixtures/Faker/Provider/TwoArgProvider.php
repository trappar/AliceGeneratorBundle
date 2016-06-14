<?php

namespace Trappar\AliceGeneratorBundle\Tests\SymfonyApp\TestBundle\DataFixtures\Faker\Provider;

use Trappar\AliceGeneratorBundle\Annotation as Fixture;

class TwoArgProvider
{
    /**
     * @param $value
     * @param $object
     * @return array
     * @Fixture\Faker("test")
     */
    public static function toFixture($value, $object)
    {
        return [$value, 1, get_class($object)];
    }
}
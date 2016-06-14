<?php

namespace Trappar\AliceGeneratorBundle\Tests\SymfonyApp\TestBundle\DataFixtures\Faker\Provider;

use Trappar\AliceGeneratorBundle\Annotation as Fixture;

class ArgTestProvider
{
    public static function noArgs()
    {
        return [];
    }

    /**
     * @param $value
     * @param $context
     * @param $propName
     * @return array
     */
    public static function threeArgs($value, $context, $propName)
    {
        return [$value, get_class($context), $propName];
    }
}
<?php

namespace Trappar\AliceGeneratorBundle\Tests\SymfonyApp\TestBundle\DataFixtures\Faker\Provider;

use Trappar\AliceGeneratorBundle\Annotation as Fixture;

class FooProvider
{
    /**
     * @param $value
     * @return array
     * @Fixture\Faker("test")
     */
    public static function toFixture($value)
    {
        return ['blah', 1, true];
    }

    /**
     * @return array
     */
    public static function testMethod1()
    {
    }

    /**
     * @Fixture\Faker()
     */
    public static function testMethod2()
    {
    }

    /**
     * @Fixture\Faker(name="test", valueAsArgs=true)
     */
    public static function testMethod3()
    {
    }
}
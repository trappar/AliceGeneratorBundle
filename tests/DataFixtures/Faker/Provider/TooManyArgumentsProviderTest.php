<?php

namespace Trappar\AliceGeneratorBundle\Tests;

use Trappar\AliceGeneratorBundle\Tests\SymfonyApp\TestBundle\Entity\ProviderTester;
use Trappar\AliceGeneratorBundle\Tests\Test\FixtureGeneratorTestCase;

class TooManyArgumentsProviderTest extends FixtureGeneratorTestCase
{
    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessageRegExp /accept a maximum of \d+ arguments/
     */
    public function test()
    {
        $this->fixtureGenerator->addProvider(new self());
        $test         = new ProviderTester();
        $test->object = new \Exception();

        $this->fixtureGenerator->generateYaml($test);
    }

    public function toFixture(\Exception $exception, $too, $many, $args, $cant, $handle)
    {
    }
}
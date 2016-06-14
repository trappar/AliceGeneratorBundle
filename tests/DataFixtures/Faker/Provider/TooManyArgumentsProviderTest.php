<?php

namespace Trappar\AliceGeneratorBundle\Tests;

use Trappar\AliceGeneratorBundle\Tests\SymfonyApp\TestBundle\Entity\ProviderTester;
use Trappar\AliceGeneratorBundle\Tests\Test\FixtureGeneratorTestCase;

class TooManyArgumentsProviderTest extends FixtureGeneratorTestCase
{
    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessageRegExp /maximum of 2 arguments/
     */
    public function test()
    {
        $this->fixtureGenerator->addProvider(new self());
        $test         = new ProviderTester();
        $test->object = new \Exception();

        $this->fixtureGenerator->generateYaml($test);
    }

    public function toFixture(\Exception $exception, $more, $args)
    {
    }
}
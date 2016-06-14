<?php

namespace Trappar\AliceGeneratorBundle\Tests;

use Trappar\AliceGeneratorBundle\Tests\SymfonyApp\TestBundle\Entity\ProviderTester;
use Trappar\AliceGeneratorBundle\Tests\Test\FixtureGeneratorTestCase;

class NoArgumentProviderTest extends FixtureGeneratorTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /must contain at least one argument/
     */
    public function test()
    {
        $this->fixtureGenerator->addProvider(new self());
        $test         = new ProviderTester();
        $test->object = new \Exception();

        $this->fixtureGenerator->generateYaml($test);
    }

    public function toFixture()
    {
    }
}
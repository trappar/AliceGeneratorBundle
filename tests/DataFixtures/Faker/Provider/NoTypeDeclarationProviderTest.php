<?php

namespace Trappar\AliceGeneratorBundle\Tests;

use Trappar\AliceGeneratorBundle\Tests\SymfonyApp\TestBundle\Entity\ProviderTester;
use Trappar\AliceGeneratorBundle\Tests\Test\FixtureGeneratorTestCase;

class NoTypeDeclarationProviderTest extends FixtureGeneratorTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /must have an object type declaration/
     */
    public function test()
    {
        $this->fixtureGenerator->addProvider(new self());
        $test         = new ProviderTester();
        $test->object = new \Exception();

        $this->fixtureGenerator->generateYaml($test);
    }

    public function toFixture($foo)
    {
    }
}
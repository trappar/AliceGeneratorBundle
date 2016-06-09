<?php

namespace Trappar\AliceGeneratorBundle\Tests\Command;

use Symfony\Component\Filesystem\Filesystem;
use Trappar\AliceGeneratorBundle\Tests\SymfonyApp\TestBundle\Entity\User;
use Trappar\AliceGeneratorBundle\Tests\Test\FixtureGeneratorTestCase;

class FixtureGeneratorCommandTest extends FixtureGeneratorTestCase
{
    public function test()
    {
        $generateFileLocation = __DIR__ . '/../SymfonyApp/TestBundle/DataFixtures/ORM/generated.yml';

        $filesystem = new Filesystem();
        $filesystem->remove(__DIR__ . '/../SymfonyApp/TestBundle/DataFixtures');

        $this->runConsole('generate:fixtures');

        $this->assertFileExists($generateFileLocation);
        
        $parsed = $this->parseYaml(file_get_contents($generateFileLocation));
        
        $this->assertEquals(1, count($parsed));
        $this->assertEquals(1, count($parsed[User::class]));
    }
}
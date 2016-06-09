<?php

namespace Trappar\AliceGeneratorBundle\Tests\Command;

use Symfony\Component\Filesystem\Filesystem;
use Trappar\AliceGeneratorBundle\Tests\SymfonyApp\TestBundle\Entity\Post;
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

        $this->assertSame(1, count($parsed));
        $this->assertSame(1, count($parsed[Post::class]));
    }
}
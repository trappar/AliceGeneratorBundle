<?php

namespace Trappar\AliceGeneratorBundle\Tests\Test;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;
use Trappar\AliceGeneratorBundle\DataFixtures\Faker\Provider\DateTimeProvider;
use Trappar\AliceGeneratorBundle\FixtureGenerator;

abstract class FixtureGeneratorTestCase extends KernelTestCase
{
    /**
     * @var Application
     */
    protected $application;

    /**
     * @var FixtureGenerator
     */
    protected $fixtureGenerator;

    /**
     * @var EntityManager
     */
    protected $em;

    public function setUp()
    {
        self::bootKernel();

        $this->application = new Application(self::$kernel);
        $this->application->setAutoExit(false);

        $this->runConsole('doctrine:database:drop', ['--force' => true]);
        $this->runConsole('doctrine:schema:create');

        $this->fixtureGenerator = static::$kernel->getContainer()->get('trappar_alice_generator.fixture_generator');
        $this->em               = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');
    }

    /**
     * Helper to run a Symfony command.
     *
     * @param string $command
     * @param array  $options
     *
     * @return int
     *
     * @throws \Exception
     */
    protected function runConsole($command, array $options = [])
    {
        $options['-e'] = 'test';
        $options['-q'] = null;
        $options       = array_merge($options, ['command' => $command]);

        return $this->application->run(new ArrayInput($options));
    }

    protected function parseYaml($yaml)
    {
        $yamlParser = new Yaml();

        return $yamlParser->parse($yaml);
    }

    protected function assertYamlEquals($expected, $yaml)
    {
        $this->assertSame($expected, $this->parseYaml($yaml));
    }

    protected function writeYaml($yml)
    {
        $fixturePath = __DIR__ . '/../SymfonyApp/TestBundle/DataFixtures/ORM';

        $fs = new Filesystem();
        $fs->mkdir($fixturePath);
        $fs->dumpFile($fixturePath . '/generated.yml', $yml);
    }
}

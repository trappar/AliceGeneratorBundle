<?php

namespace Trappar\AliceGeneratorBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Trappar\AliceGeneratorBundle\FixtureGenerator;

abstract class AbstractFixtureGeneratorCommand extends Command
{
    /**
     * @var FixtureGenerator
     */
    protected $fixtureGenerator;

    /**
     * @param FixtureGenerator $fixtureGenerator
     */
    public function setFixtureGenerator(FixtureGenerator $fixtureGenerator)
    {
        $this->fixtureGenerator = $fixtureGenerator;
    }

    protected function writeYaml($yaml, OutputInterface $output, $outputLocation)
    {
        $filesystem      = new Filesystem();
        $outputDirectory = pathinfo($outputLocation, PATHINFO_DIRNAME);

        $output->write("Writing generated fixtures to '$outputLocation' ... ");

        if (!$filesystem->exists($outputDirectory)) {
            $filesystem->mkdir($outputDirectory);
        }
        $filesystem->dumpFile($outputLocation, $yaml);

        $output->writeln('OK!');
    }
}
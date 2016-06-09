<?php

namespace Trappar\AliceGeneratorBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Trappar\AliceGeneratorBundle\FixtureGenerationContext;
use Trappar\AliceGeneratorBundle\FixtureGenerator;

abstract class AbstractFixtureGeneratorCommand extends Command implements FixtureGeneratorCommandInterface
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

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filesystem = new Filesystem();
        $entities = $this->getEntities($input);
        $context = $this->getFixtureGenerationContext();
        $outputLocation = $this->getOutputLocation();
        $outputDirectory = pathinfo($outputLocation, PATHINFO_DIRNAME);

        $yaml = $this->fixtureGenerator->generateYaml($entities, $context);
        
        $output->write("Writing generated fixtures to '$outputLocation' ... ");
        
        if (!$filesystem->exists($outputDirectory)) {
            $filesystem->mkdir($outputDirectory);
        }
        $filesystem->dumpFile($outputLocation, $yaml);
        
        $output->writeln('OK!');
    }

    /**
     * Override this method if you want more control over how fixtures are generated
     * 
     * @return FixtureGenerationContext
     */
    protected function getFixtureGenerationContext()
    {
        return FixtureGenerationContext::create();
    }
}
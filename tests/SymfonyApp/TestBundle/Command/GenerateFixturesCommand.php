<?php

namespace Trappar\AliceGeneratorBundle\Tests\SymfonyApp\TestBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Trappar\AliceGeneratorBundle\Command\AbstractFixtureGeneratorCommand;
use Trappar\AliceGeneratorBundle\Tests\SymfonyApp\TestBundle\Entity\Post;

class GenerateFixturesCommand extends AbstractFixtureGeneratorCommand
{
    /**
     * @inheritdoc
     */
    public function configure()
    {
        $this->setName('generate:fixtures');
    }

    /**
     * @inheritdoc
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $post = new Post();
        $post->title = 'test';

        $this->writeYaml(
            $this->fixtureGenerator->generateYaml($post),
            $output,
            __DIR__ . '/../DataFixtures/ORM/generated.yml'
        );
    }
}
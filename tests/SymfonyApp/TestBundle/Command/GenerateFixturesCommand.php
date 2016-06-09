<?php

namespace Trappar\AliceGeneratorBundle\Tests\SymfonyApp\TestBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Trappar\AliceGeneratorBundle\Command\AbstractFixtureGeneratorCommand;
use Trappar\AliceGeneratorBundle\Tests\SymfonyApp\TestBundle\Entity\User;

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
    public function getEntities(InputInterface $input, OutputInterface $output)
    {
        $user = new User();
        $user->setUsername('testUser');
        return $user;
    }

    /**
     * @inheritdoc
     */
    public function getOutputLocation()
    {
        return __DIR__ . '/../DataFixtures/ORM/generated.yml';
    }
}
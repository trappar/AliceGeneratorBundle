<?php

namespace Trappar\AliceGeneratorBundle\Tests\DataStorage;

use phpunit\framework\TestCase;
use Trappar\AliceGeneratorBundle\DataStorage\EntityConstraints;
use Trappar\AliceGeneratorBundle\Tests\SymfonyApp\TestBundle\Entity\User;

class EntityConstraintsTest extends TestCase
{
    public function test()
    {
        $user1 = new User();
        $user2 = new User();
        
        $entityConstraints = new EntityConstraints();
        
        $this->assertTrue($entityConstraints->checkValid($user1));
        
        $entityConstraints->add($user1);

        $this->assertTrue($entityConstraints->checkValid($user1));
        $this->assertFalse($entityConstraints->checkValid($user2));
    }
}
<?php

namespace Trappar\AliceGeneratorBundle\Tests\ReferenceNamer;

use phpunit\framework\TestCase;
use Trappar\AliceGeneratorBundle\ReferenceNamer\ClassNamer;

class ClassNamerTest extends TestCase
{
    public function testCreatePrefix()
    {
        $classNamer = new ClassNamer();
        
        $this->assertEquals(
            'ClassNamerTest-',
            $classNamer->createPrefix($this)
        );
    }
}
<?php

namespace Trappar\AliceGeneratorBundle\Tests\ReferenceNamer;

use PHPUnit\Framework\TestCase;
use Trappar\AliceGeneratorBundle\ReferenceNamer\ClassNamer;

class ClassNamerTest extends TestCase
{
    public function testCreatePrefix()
    {
        $classNamer = new ClassNamer();
        
        $this->assertSame(
            'ClassNamerTest-',
            $classNamer->createPrefix($this)
        );
    }
}
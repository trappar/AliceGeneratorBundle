<?php

namespace Trappar\AliceGeneratorBundle\Tests\ReferenceNamer;

use phpunit\framework\TestCase;
use Trappar\AliceGeneratorBundle\ReferenceNamer\NamespaceNamer;

class NamespaceNamerTest extends TestCase
{
    public function testCreatePrefix()
    {
        $namer = new NamespaceNamer();
        
        $this->assertEquals(
            'TrapparAliceGeneratorBundleTestsReferenceNamerNamespaceNamerTest-',
            $namer->createPrefix($this)
        );
        
        $namer->setIgnoredNamespaces(['Trappar', 'AliceGeneratorBundle']);

        $this->assertEquals(
            'TestsReferenceNamerNamespaceNamerTest-',
            $namer->createPrefix($this)
        );
        
        $namer->setNamespaceSeparator('-');

        $this->assertEquals(
            'Tests-ReferenceNamerNamespaceNamerTest-',
            $namer->createPrefix($this)
        );
    }
}
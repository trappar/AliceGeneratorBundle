<?php

namespace Trappar\AliceGeneratorBundle\Tests\ReferenceNamer;

use PHPUnit\Framework\TestCase;
use Trappar\AliceGeneratorBundle\ReferenceNamer\NamespaceNamer;

class NamespaceNamerTest extends TestCase
{
    public function testCreatePrefix()
    {
        $namer = new NamespaceNamer();
        
        $this->assertSame(
            'TrapparAliceGeneratorBundleTestsReferenceNamerNamespaceNamerTest-',
            $namer->createPrefix($this)
        );
        
        $namer->setIgnoredNamespaces(['Trappar', 'AliceGeneratorBundle']);

        $this->assertSame(
            'TestsReferenceNamerNamespaceNamerTest-',
            $namer->createPrefix($this)
        );
        
        $namer->setNamespaceSeparator('-');

        $this->assertSame(
            'Tests-ReferenceNamerNamespaceNamerTest-',
            $namer->createPrefix($this)
        );
    }
}
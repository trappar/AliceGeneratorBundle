<?php

namespace Trappar\AliceGeneratorBundle\Tests\DataStorage;

use PHPUnit\Framework\TestCase;
use Trappar\AliceGeneratorBundle\DataStorage\EntityCache;

class EntityCacheTest extends TestCase
{
    public function test()
    {
        $entityCache = new EntityCache();

        $testData = new \stdClass();

        $this->assertSame(
            EntityCache::OBJECT_NOT_FOUND,
            $entityCache->find($testData)
        );
        
        $entityCache->add($testData);

        $this->assertSame(
            1,
            $entityCache->find($testData)
        );

        $entityCache->skip($testData);

        $this->assertSame(
            EntityCache::OBJECT_SKIPPED,
            $entityCache->find($testData)
        );
    }
}
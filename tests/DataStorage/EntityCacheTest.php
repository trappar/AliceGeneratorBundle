<?php

namespace Trappar\AliceGeneratorBundle\Tests\DataStorage;

use phpunit\framework\TestCase;
use Trappar\AliceGeneratorBundle\DataStorage\EntityCache;

class EntityCacheTest extends TestCase
{
    public function test()
    {
        $entityCache = new EntityCache();

        $testData = new \stdClass();

        $this->assertEquals(
            EntityCache::OBJECT_NOT_FOUND,
            $entityCache->find($testData)
        );
        
        $entityCache->add($testData);

        $this->assertEquals(
            '1',
            $entityCache->find($testData)
        );

        $entityCache->skip($testData);

        $this->assertEquals(
            EntityCache::OBJECT_SKIPPED,
            $entityCache->find($testData)
        );
    }
}
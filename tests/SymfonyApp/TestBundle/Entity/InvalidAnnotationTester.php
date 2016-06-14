<?php

namespace Trappar\AliceGeneratorBundle\Tests\SymfonyApp\TestBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Trappar\AliceGeneratorBundle\Annotation as Fixture;

class InvalidAnnotationTester
{
    /** @Fixture\Data */
    public $invalidDataValue;

    /** @Fixture\Faker() */
    public $invalidFakerName;

    /** @Fixture\Faker("test", class="notRealClass") */
    public $invalidFakerClass;

    /** @Fixture\Faker("test", class="InvalidAnnotationTester") */
    public $invalidFakerClassNoToFixtureMethod;

    /** @Fixture\Faker("test", class="Trappar\AliceGeneratorBundle\Tests\SymfonyApp\TestBundle\DataFixtures\Faker\Provider\NonArrayReturningProvider") */
    public $invalidFakerReturnsNonArray;

    /** @Fixture\Faker("test", service="not_real_service") */
    public $invalidFakerService;

    /** @Fixture\Faker("test", service="service_container") */
    public $invalidFakerServiceNoToFixtureMethod;

    /** @Fixture\Faker("test", class="something", service="something") */
    public $invalidFakerMultipleAttributes;

    /**
     * @Fixture\Data("test")
     * @Fixture\Faker("test")
     */
    public $invalidMultipleAnnotations;


    public function invalidFakerOnMethodNoAnnotation()
    {
    }

    /**
     * @Fixture\Faker(name="test", valueAsArgs=true)
     */
    public function invalidFakerOnMethodAttribute()
    {
    }
}


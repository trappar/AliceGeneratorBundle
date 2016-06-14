<?php

namespace Trappar\AliceGeneratorBundle\Tests\SymfonyApp\TestBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Trappar\AliceGeneratorBundle\Annotation as Fixture;

/**
 * @ORM\Entity()
 */
class ValidAnnotationTester
{
    /**
     * @ORM\Column
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public $id;

    /**
     * @ORM\Column(name="data", type="string")
     * @Fixture\Data("test")
     */
    public $data;

    /**
     * @ORM\Column
     * @Fixture\Faker("test")
     */
    public $fakerBasic;

    /**
     * @ORM\Column
     * @Fixture\Faker("test", arguments={"test", true})
     */
    public $fakerArgs;

    /**
     * @ORM\Column
     * @Fixture\Faker("test", class="ValidAnnotationTester")
     */
    public $fakerClassSelf;

    /**
     * @ORM\Column
     * @Fixture\Faker("test", class="Trappar\AliceGeneratorBundle\Tests\SymfonyApp\TestBundle\DataFixtures\Faker\Provider\ArgTestProvider::noArgs")
     */
    public $fakerClassWithMethod;

    /**
     * @ORM\Column
     * @Fixture\Faker("test", class="Trappar\AliceGeneratorBundle\Tests\SymfonyApp\TestBundle\DataFixtures\Faker\Provider\ArgTestProvider::threeArgs")
     */
    public $fakerClassThreeArgs;

    /**
     * @ORM\Column
     * @Fixture\Faker("test", service="faker.provider.service")
     */
    public $fakerService;

    /**
     * @ORM\Column
     * @Fixture\Faker("test", valueAsArgs=true)
     */
    public $fakerValueAsArgs;

    /**
     * @ORM\Column
     * @Fixture\Ignore()
     */
    public $fakerIgnore;

    /**
     * @Fixture\Faker("test")
     */
    public static function toFixture()
    {
        return [];
    }
}
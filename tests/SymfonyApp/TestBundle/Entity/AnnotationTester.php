<?php

namespace Trappar\AliceGeneratorBundle\Tests\SymfonyApp\TestBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Trappar\AliceGeneratorBundle\Annotation as Fixture;

/**
 * @ORM\Entity()
 */
class AnnotationTester
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public $id;
    
    /**
     * @var string
     * @ORM\Column(name="a", type="string")
     * @Fixture\Data("test")
     */
    public $a;

    /**
     * @var string
     * @ORM\Column(name="b", type="string")
     * @Fixture\Faker("test")
     */
    public $b;
    
    /**
     * @var string
     * @ORM\Column(name="c", type="string")
     * @Fixture\Faker("test", arguments={"test", true})
     */
    public $c;

    /**
     * @var string
     * @ORM\Column(name="d", type="string")
     * @Fixture\Faker("test", class="Trappar\AliceGeneratorBundle\Tests\SymfonyApp\TestBundle\DataFixtures\Faker\Provider\FooProvider")
     */
    public $d;

    /**
     * @var string
     * @ORM\Column(name="e", type="string")
     * @Fixture\Faker("test", service="faker.provider.foo")
     */
    public $e;

    /**
     * @var string
     * @ORM\Column(name="f", type="string")
     * @Fixture\Faker("test", valueAsArgs=true)
     */
    public $f;

    /**
     * @var
     * @ORM\Column(name="g", type="string")
     * @Fixture\Ignore()
     */
    public $g;
}


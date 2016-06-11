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
    private $id;
    
    /**
     * @var string
     * @ORM\Column(name="a", type="string")
     * @Fixture\Data("test")
     */
    private $a;

    /**
     * @var string
     * @ORM\Column(name="b", type="string")
     * @Fixture\Faker("test")
     */
    private $b;
    
    /**
     * @var string
     * @ORM\Column(name="c", type="string")
     * @Fixture\Faker("test", arguments={"test", true})
     */
    private $c;

    /**
     * @var string
     * @ORM\Column(name="d", type="string")
     * @Fixture\Faker("test", class="Trappar\AliceGeneratorBundle\Tests\SymfonyApp\TestBundle\DataFixtures\Faker\Provider\FooProvider")
     */
    private $d;

    /**
     * @var string
     * @ORM\Column(name="e", type="string")
     * @Fixture\Faker("test", service="faker.provider.foo")
     */
    private $e;

    /**
     * @var
     * @ORM\Column(name="f", type="string")
     * @Fixture\Ignore()
     */
    private $f;
}


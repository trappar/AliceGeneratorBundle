<?php

namespace Trappar\AliceGeneratorBundle\Tests\SymfonyApp\TestBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class ProviderTester
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
     * @ORM\Column(name="created", type="datetime")
     */
    public $created;

    /**
     * @var \stdClass
     * @ORM\Column(name="object", type="string")
     */
    public $object;
}


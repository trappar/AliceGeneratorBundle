<?php

namespace Trappar\AliceGeneratorBundle\Tests\SymfonyApp\TestBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Trappar\AliceGenerator\Annotation as Fixture;

/**
 * Post
 *
 * @ORM\Table(name="post")
 * @ORM\Entity()
 */
class Post
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
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    public $title;

    /**
     * @var string
     *
     * @ORM\Column(name="body", type="text")
     * @Fixture\Faker("paragraphs", type="array", arguments={3, true})
     */
    public $body;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="posts")
     */
    public $postedBy;
}


<?php

namespace Trappar\AliceGeneratorBundle\Tests\SymfonyApp\TestBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Trappar\AliceGeneratorBundle\Annotation as Fixture;

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
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="body", type="text")
     * @Fixture\Faker("paragraphs", arguments={3, true})
     */
    private $body;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="posts")
     */
    private $postedBy;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Post
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set body
     *
     * @param string $body
     *
     * @return Post
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get body
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set postedBy
     *
     * @param User $postedBy
     *
     * @return Post
     */
    public function setPostedBy($postedBy)
    {
        $this->postedBy = $postedBy;

        return $this;
    }

    /**
     * Get postedBy
     *
     * @return \stdClass
     */
    public function getPostedBy()
    {
        return $this->postedBy;
    }
}


<?php

namespace Trappar\AliceGeneratorBundle\Tests\SymfonyApp\TestBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Trappar\AliceGeneratorBundle\Annotation as Fixture;

/**
 * User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity()
 */
class User
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
     * @ORM\Column(name="username", type="string", length=100, unique=true)
     */
    public $username;

    /**
     * @var string
     * 
     * @ORM\Column(name="password", type="string")
     * @Fixture\Data("test")
     */
    public $password;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     */
    public $email;

    /**
     * @var array
     * 
     * @ORM\Column(name="roles", type="simple_array")
     */
    public $roles = ['ROLE_USER'];
    
    /**
     * @var Post[]
     * 
     * @ORM\OneToMany(targetEntity="Post", mappedBy="postedBy", cascade={"persist"})
     */
    public $posts;

    /**
     * Just here for testing
     * @var ProviderTester
     * @ORM\OneToOne(targetEntity="ProviderTester")
     */
    public $providerTester1;
    
    /**
     * Just here for testing
     * @var ProviderTester
     * @ORM\OneToOne(targetEntity="ProviderTester")
     */
    public $providerTester2;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
    }
}


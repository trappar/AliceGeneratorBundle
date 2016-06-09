# Advanced usage

## Custom Fixture Generation Console Command

Start by creating the command class, for example:

```php
<?php

//AppBundle\Command\GenerateFixturesCommand.php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Trappar\AliceGeneratorBundle\Command\AbstractFixtureGeneratorCommand;

class GenerateFixturesCommand extends AbstractFixtureGeneratorCommand
{
    /**
     * @var EntityManager
     */
    private $em;
    
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }
    
    /**
     * @inheritdoc
     */
    public function configure()
    {
        $this->setName('generate:fixtures');
    }

    /**
     * @inheritdoc
     */
    public function getEntities(InputInterface $input)
    {
        // Implement code here to fetch some entities for example
        $users = $this->em->getRepository('AppBundle:User')->findAll()
        $posts = $this->em->getRepository('AppBundle:Post')->findAll()
        
        return [$users, $posts];
    }

    /**
     * @inheritdoc
     */
    public function getOutputLocation()
    {
        return __DIR__ . '/../DataFixtures/ORM/generated.yml';
    }
}
```

For information about the `configure` method reference ["How to Create a Console Command"](http://symfony.com/doc/current/cookbook/console/console_command.html)

You may also override the `getFixtureGenerationContext` method in your console command to specify options for how
fixtures will be generated. See (#setting-options-for-fixture-generation)

## Fixture Generation Contexts

Since generating fixtures involves recursing through entities, you will often find yourself in a situation where generating
fixtures yields quite a lot more information than you would like - this is just the nature of recursion! To control this
you can pass an optional second parameter to `FixtureGenerator::generateYaml` called a `FixtureGenerationContext`. This object
offers several controls which can help you produce the results you're after.

`setMaximumRecursion` - allows you to limit the recursion depth. Example:

```php
// For an entity like...
$post->getUser()->getGroup();

// Will include only the Post
$fixtureGenerator->generateYaml($post, FixtureGenerationContext::create()->setMaximumRecursion(0));

// Will include the Post and User
$fixtureGenerator->generateYaml($post, FixtureGenerationContext::create()->setMaximumRecursion(1));
```

`addEntityConstraint` - allows you to limit any entity type to those that you specify. Example:

```php
// For an entity like...
$post->getUser()->getPosts();

// Since this User may have many Posts, this will result in every Post the User is related to being included in the generated fixtures.
$fixtureGenerator->generateYaml($post);

// Now since $post is an entity constraint, all other posts will be ignored.
$fixtureGenerator->generateYaml($post, FixtureGenerationContext::create()->addEntityConstraint($post));
```

`setReferenceNamer` - allows you to specify a custom class for handling reference names. Example:

```php
// Same as above
$post = new AppBundle\Entity\Post;

// Using the default ClassNamer references will be like "Post-1" 
$fixtureGenerator->generateYaml($post);

// Using the alternative NamespaceNamer with default options ignores "AppBundle" and "Entity", so references will still be like "Post-1"
$namer = new Trappar\AliceGeneratorBundle\ReferenceNamer\NamespaceNamer();
$fixtureGenerator->generateYaml($post, FixtureGenerationContext::create()->setReferenceNamer($namer));

// You can set options of NamespaceNamer...
$namer->setIgnoredNamespaces(['AppBundle']);
$namer->setNamespaceSeparator('-');

// Now references will be like "Entity-Post-1"
$fixtureGenerator->generateYaml($post, FixtureGenerationContext::create()->setReferenceNamer($namer));
```

Previous chapter: [Basic usage](../../../README.md#basic-usage)
Next chapter: [Custom Type Providers](type-providers.md)
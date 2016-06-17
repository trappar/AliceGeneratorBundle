AliceGeneratorBundle
===========

A [Symfony](http://symfony.com) bundle to recursively convert existing [Doctrine](http://doctrine-project.org) entities into
[Alice](https://github.com/nelmio/alice) Fixtures.

## Why?

Sometimes you find yourself working on a large project with many tables where there are no existing fixtures.
In this case even though Alice makes fixtures much easier to write, that process can still be extremely time consuming.

This bundle proposes an alternate starting point - *automatically generate fixtures from your existing data.*

This opens up a whole new, much faster way to get your test data established... just enter it in your user interface!

## Example

Let's say you have the following entities

```php
// AppBundle/Entity/Post
class Post
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    /** @ORM\Column(name="title", type="string", length=255) */
    private $title;
    /** @ORM\Column(name="bodya", type="text") */
    private $body;
    /** @ORM\ManyToOne(targetEntity="User", inversedBy="posts") */
    private $postedBy;
}

class User
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    /** @ORM\Column(name="username", type="string", length=100, unique=true) */
    private $username;
}
```

This bundle let's you turn that directly into...

```yaml
AppBundle\Entity\Post:
    Post-1:
        title: 'Is Making Fixtures Too Time Consuming'
        body: 'Check out AliceBundle!'
        postedBy: '@User-1'
    Post-2:
        title: 'Too Much Data to Hand Write?'
        body: 'Check out AliceGeneratorBundle!'
        postedBy: '@User-1'
AppBundle\Entity\User:
    User-1:
        username: testUser
```

Use your UI to create your data, let this bundle do the hard part, and then tweak until you've got perfect fixtures :)

## Documentation

1. [Install](#installation)
2. [Basic usage](#basic-usage)
3. [Advanced Usage](src/Resources/doc/advanced-usage.md)
    1. [Custom Fixture Generation Console Command](src/Resources/doc/advanced-usage.md#custom-fixture-generation-console-command)
    2. [Fixture Generation Contexts](src/Resources/doc/advanced-usage.md#fixture-generation-contexts)
4. [Annotations](src/Resources/doc/annotations.md)
    1. [Data](src/Resources/doc/annotations.md#data-annotation)
    2. [Faker](src/Resources/doc/annotations.md#faker-annotation)
    3. [Ignore](src/Resources/doc/annotations.md#ignore-annotation)
5. [Custom Providers](src/Resources/doc/custom-providers.md)
6. [Resources](#resources)

## Installation

This bundle is intended to be used alongside the [AliceBundle](https://github.com/hautelook/AliceBundle). You should have
a solid understanding of how that ecosystem works before installing this bundle.

You can use [Composer](https://getcomposer.org/) to install the bundle to your project:

```bash
composer require --dev trappar/alice-generator-bundle
```

Then, enable the bundle by updating your `app/AppKernel.php` file to enable the bundle:

```php
<?php
// app/AppKernel.php

public function registerBundles()
{
    //...
    if (in_array($this->getEnvironment(), ['dev', 'test'])) {
        //...
        $bundles[] = new Trappar\AliceGeneratorBundle\TrapparAliceGeneratorBundle();
    }

    return $bundles;
}
```

## Basic usage

There are two primary ways to use this bundle.

1. Use the `trappar_alice_generator.fixture_generator` service's `generateYaml` method directly. You can pass a single entity or an
array/Collection of any number of entities to this and it will produce a string of yaml fixtures.
2. Create a console command to handle fixture generation.

See more in [Advanced Usage](src/Resources/doc/advanced-usage.md)

## Resources

* [Changelog](CHANGELOG.md)
* [AliceBundle](https://github.com/hautelook/AliceBundle)
* [Alice](https://github.com/nelmio/alice)
* [Faker](https://github.com/fzaninotto/Faker)

## Credits

This bundle was developped by [Jeff Way](https://github.com/trappar) with quite a lot of inspiration from [AliceBundle](https://github.com/hautelook/AliceBundle).

[Other contributors](https://github.com/trappar/AliceGeneratorBundle/graphs/contributors).

## License

[![license](https://img.shields.io/badge/license-MIT-red.svg?style=flat-square)](Resources/meta/LICENSE)

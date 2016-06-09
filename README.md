AliceGeneratorBundle
===========

A [Symfony](http://symfony.com) bundle to convert existing [Doctrine](http://doctrine-project.org) entities into
[Alice](https://github.com/nelmio/alice) Fixtures.

## Why?

Sometimes you might find yourself working on a large project with many tables where there are no existing fixtures.
In this case even though Alice makes fixtures much easier to write, that process can still be extremely time consuming.

This bundle proposes an alternate starting point - *automatically generate fixtures from your existing data.*

This opens up a whole new, much faster way to get your test data established... just enter it in your user interface!

## Documentation

1. [Install](#installation)
2. [Basic usage](#basic-usage)
3. [Advanced Usage](src/Resources/doc/advanced-usage.md)
    1. [Custom Fixture Generation Console Command](src/Resources/doc/advanced-usage.md#custom-fixture-generation-console-command)
    2. [Fixture Generation Contexts](src/Resources/doc/advanced-usage.md#fixture-generation-contexts)
4. [Custom Type Providers](src/Resources/doc/type-providers.md)
5. [Resources](#resources)

## Installation

This bundle is intended to be used alongside the [AliceBundle](https://github.com/hautelook/AliceBundle). You'll need to
use either that or some

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

1. Use the `trappar_alice_generator.fixture_generator` service's `generateYaml` method directly.
2. Create a console command to handle fixture generation.

See more in [Advanced Usage](src/Resources/doc/advanced-usage.md)

## Resources

* [Changelog](CHANGELOG.md)

## Credits

This bundle was developped by [Jeff Way](https://github.com/trappar) with quite a lot of inspiration from [AliceBundle](https://github.com/hautelook/AliceBundle).

[Other contributors](https://github.com/trappar/AliceGeneratorBundle/graphs/contributors).

## License

[![license](https://img.shields.io/badge/license-MIT-red.svg?style=flat-square)](Resources/meta/LICENSE)

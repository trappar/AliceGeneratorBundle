AliceGeneratorBundle [![Build Status](https://travis-ci.org/trappar/AliceGeneratorBundle.svg?branch=master)](https://travis-ci.org/trappar/AliceGeneratorBundle)
====================

This bundle integrates the [AliceGenerator](https://github.com/trappar/AliceGenerator) library into Symfony.

## Introduction

TrapparAliceGeneratorBundle allows you to generate Alice Fixtures from your existing data.

You can learn more in the [documentation for the standalone library](https://github.com/trappar/AliceGenerator).

## Table of Contents

* [Installation](#installation)
* [Configuration](#configuration)
  * [Full Configuration Reference](src/Trappar/AliceGeneratorBundle/Resources/doc/configuration.md)
* [Usage](#usage)
* [Resources](#resources)
* [Credits](#credits)
* [License](#license)

## Installation

```bash
composer require trappar/alice-generator-bundle
```

Then, enable the bundle by updating your `app/AppKernel.php` file to enable the bundle:

```php
<?php
// in AppKernel::registerBundles()

if (in_array($this->getEnvironment(), ['dev', 'test'])) {
    // ...
    $bundles[] = new Trappar\AliceGeneratorBundle\TrapparAliceGeneratorBundle();
    // ...
}
```

## Configuration

TrapparAliceGeneratorBundle requires no initial configuration to get you started.

For all available configuration options, please see the [configuration reference](src/Trappar/AliceGeneratorBundle/Resources/doc/configuration.md).

## Usage

The main method for using this bundle is the included command line application. Use this by running:

```bash
console generate:fixtures
```

And simply follow along with the prompts.

You can also request the FixtureGenerator as a service from the container:

```php
$fixtureGenerator = $container->get('trappar_alice_generator.fixture_generator');
$yaml = $fixtureGenerator->generateYaml($entities);
```

Learn more in the [documentation for the dedicated library](https://github.com/trappar/AliceGenerator/blob/master/doc/usage.md).

## Resources

* [Changelog](CHANGELOG.md)
* [AliceGenerator](https://github.com/trappar/AliceGenerator)
* [Alice](https://github.com/nelmio/alice)
* [AliceBundle](https://github.com/hautelook/AliceBundle)
* [Faker](https://github.com/fzaninotto/Faker)

## Credits

This bundle was developped by [Jeff Way](https://github.com/trappar) with quite a lot of inspiration from:

* [AliceBundle](https://github.com/hautelook/AliceBundle)
* [JMSSerializerBundle](https://github.com/schmittjoh/JMSSerializerBundle)
* [SensioGeneratorBundle](https://github.com/sensiolabs/SensioGeneratorBundle)

[Other contributors](https://github.com/trappar/AliceGeneratorBundle/graphs/contributors).

## License

[![license](https://img.shields.io/badge/license-MIT-red.svg?style=flat-square)](Resources/meta/LICENSE)

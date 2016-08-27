# Upgrade Guide

## Upgrading from 0.1 to 1.0

##### AbstractFixtureGenerationCommand is no longer provided

If you wrote a command based on this you can do one of:

* Reimplement your custom command
  * Inject the FixtureGenerator service manually into the constructor, or retrieve it from the container (this was previously handled by the abstract service)
  * Handle writing the yaml file yourself. This can be as simple as using `file_put_contents`.
* Remove your custom command
  * If your command was fairly simple then it's likely that the functionality is now offered natively through the bundle's new `generate:fixtures` command.

##### Annotations have moved to the AliceGenerator library

You will need to replace use statements resembling

```php
<?php
use Trappar\AliceGeneratorBundle\Annotation as Fixture;
```

with
 
```php
<?php
use Trappar\AliceGenerator\Annotation as Fixture;
```

##### The format of the Faker annotation has changed

Reference the [AliceGenerator documentation - Property Metadata section](https://github.com/trappar/AliceGenerator/blob/master/doc/metadata.md#faker) for more information.

##### Custom Providers have become Custom Object Handlers

Reference the [AliceGenerator documentation - Custom Object Handlers section](https://github.com/trappar/AliceGenerator/blob/master/doc/custom-object-handlers.md) for more information.

##### It is no longer recommended to require this bundle as a dev package in composer

This never really worked in the first place, since any use of annotations would cause errors in production when those annotations could not be found. To fix, simply move this bundle from the "require-dev" section of the composer.json to the "require" section.
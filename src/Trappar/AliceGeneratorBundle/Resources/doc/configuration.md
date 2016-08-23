# Configuration

## Custom Object Handlers

You can register any service as an object handler by adding tag `trappar_alice_generator.object_handler`

```yaml
# app/config/services.yml

services:
    object_handler.my_handler:
        class: AppBundle\ObjectHandler\MyHandler
        tags: [ { name: trappar_alice_generator.object_handler } ]
```

Any object handlers added this way will be called before the built-in object handlers. This means that, for example, if you wish to override the native behavior for handling `DateTime` objects, you can do so.

For more information on Custom Object Handlers, check out the [standalone library's documentation](https://github.com/trappar/AliceGenerator/blob/master/doc/custom-object-handlers.md).

## Overriding Third-Party Metadata

Sometimes you want to generate fixtures for objects which are shipped by a third-party bundle. Such a third-party bundle might not ship with metadata that suits your needs, or possibly none, at all. In such a case, you can override the default location that is searched for metadata with a path that is under your control.

```yaml
trappar_alice_generator:
    metadata:
        directories:
            FOSUB:
                namespace_prefix: "FOS\\UserBundle"
                path: "%kernel.root_dir%/fixture/FOSUB"
```

## Extension Reference

Below you find a reference of all configuration options with their default values:

```yaml
# config.yml
trappar_alice_generator:
    metadata:
        # Using auto-detection, the mapping files for each bundle will be
        # expected in the Resources/config/fixture directory.
        #
        # Example:
        # class: My\FooBundle\Entity\User
        # expected path: @MyFooBundle/Resources/config/fixture/Entity.User.yml
        auto_detection: true

        # if you don't want to use auto-detection, you can also define the
        # namespace prefix and the corresponding directory explicitly
        directories:
            any-name:
                namespace_prefix: "My\\FooBundle"
                path: "@MyFooBundle/Resources/config/fixture"
            another-name:
                namespace_prefix: "My\\BarBundle"
                path: "@MyBarBundle/Resources/config/fixture"
    yaml:
        # These settings directly control the arguments for \Symfony\Component\Yaml\Yaml::dump().
        inline: 3
        indent: 4
```

[Back to the README](/README.md)
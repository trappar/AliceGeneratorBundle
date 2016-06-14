# Annotations

Often you won't want to use the exact data in your entities when generating fixtures. For example, let's say you're
generating fixtures for a User in your system - you wouldn't want to dump their actual password hash in a generated
fixture. You would probably instead like to use some pre-determined test data. This bundle offers two annotations
to make this easier.

## Data Annotation

The Data annotation allows you to specify an alternate static value that will always be used for a particular property.
 
```php
<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Trappar\AliceGeneratorBundle\Annotation as Fixture;

class User
{
    /**
     * @var string
     * @ORM\Column(name="email", type="string")
     * @Fixture\Data("test@gmail.com")
     */
    private $email;
}
```

Will generate the following no matter what actual email address is contained in the entity.

```yaml
AppBundle\Entity\User:
    User-1:
        email: 'test@gmail.com'
```

## Faker Annotation

The Faker annotation allows you to specify a faker provider which will be used in place of the actual value of a
property. There are several ways to use this annotation. You can have it pass a static set of arguments, pass the value
of the property as a single argument, a service with a toFixture() method, or a class with a static toFixture
(or any other custom named method).

This is a main point of customization for this bundle, and it's extremely powerful. Keep this in mind while you're
working with your data.

#### Usage examples:

**Pass a static set of arguments**

```php
<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Trappar\AliceGeneratorBundle\Annotation as Fixture;

class Post
{
    /**
     * @var string
     * @ORM\Column(name="body", type="text")
     * @Fixture\Faker("paragraphs", arguments={3, true})
     */
    private $body;
}
```

Will generate the following

```yaml
AppBundle\Entity\Post:
    Post-1:
        body: <paragraphs(3, true)>
```

**Pass property value as argument**

```php
<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Trappar\AliceGeneratorBundle\Annotation as Fixture;

class Post
{
    /**
     * @var string
     * @ORM\Column(name="title", type="string")
     * @Fixture\Faker("shuffle", valueAsArgs=true)
     */
    private $title = 'My Title';
}
```

Will generate the following

```yaml
AppBundle\Entity\Post:
    Post-1:
        title: <shuffle("My Title")>
```

**Pass a service**

```php
/**
 * Call a custom service's toFixture method
 * @Fixture\Faker("custom", service="faker.provider.custom")
 */
 protected $something;
```

Will call a method like

```php
<?php

namespace AppBundle\DataFixtures\Faker\Provider;

class CustomFakerProvider
{
    public function toFixture($value, $contextObject) {
        return [1, true];
    }
}
```

Which will result in the following fixture

```yaml
AppBundle\User:
    User-1
        something: <custom(1, true)> 
```

**Pass a class**

```php
/**
 * Call a custom class's toFixture method
 * @Fixture\Faker("custom", class="AppBundle\Helper\CustomFixtureFormatter")
 *
 * Or even call a specific method on a custom class
 * @Fixture\Faker("custom", class="AppBundle\Helper\CustomFixtureFormatter::customMethod")
 */
 protected $something;
```

Will call methods like

```php
<?php

namespace AppBundle\Helper;

class CustomFixtureFormatter
{
    public static function toFixture($value) {
        return [strtoupper($value)];
    }
    
    public static function customMethod($value) {
        return ['This value is so customized: ' . $value];
    }
}
```

Which will result in the following fixture

```yaml
AppBundle\User:
    User-1
        something: SOME VALUE
            OR
        something: 'This value is so customized: Some Value'
```

### toFixture Definition

When using the service or class attributes on @Faker, a custom method will be called. Here's the signature for that method:

```php
array toFixture ( [ mixed $value [ , object $context ]] )
```

You can choose to accept the value, context object, or neither depending on what information you need.

## Ignore Annotation

Sometimes you just never want a particular property in an entity to be dumped to the fixtures. Maybe you have a field
where a cron automatically updates some derived data on a periodic basis - why put this in a fixture if it's just going
to need to be derived again anyway? This is why there is a Ignore annotation.

```php
<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Trappar\AliceGeneratorBundle\Annotation as Fixture;

class User
{
    /**
     * @var string
     * @ORM\Column(name="daysSinceCreated", type="string")
     * @Fixture\Ignore
     */
    private $daysSinceCreated;
}
```

Not much yaml to show here since this property won't show up in generated fixtures at all!

Previous chapter: [Advanced Usage](advanced-usage.md)<br />
Next chapter: [Custom Providers](custom-providers.md)
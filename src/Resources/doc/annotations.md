# Annotations

Often you won't want to use the exact data in your entities when generating fixtures. For example, let's say you're
generating fixtures for a User in your system - you wouldn't want to dump their actual password hash in a generated
fixture. You would probably instead like to use some pre-determined test data. This bundle offers two annotations
to make this easier.

## FixtureData Annotation

The FixtureData annotation allows you to specify an alternate static value that will always be used for a particular property.
 
```php
<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Trappar\AliceGeneratorBundle\Annotation\FixtureData;

class User
{
    /**
     * @var string
     * @ORM\Column(name="email", type="string")
     * @FixtureData("test@gmail.com")
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

## FixtureFaker Annotation

The FixtureFaker annotation allows you to specify a faker provider which will be used in place of the actual value of a property.

```php
<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Trappar\AliceGeneratorBundle\Annotation\FixtureFaker;

class Post
{
    /**
     * @var string
     * @ORM\Column(name="body", type="text")
     * @FixtureFaker("paragraphs", arguments={3, true})
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

You can also pass a special string "$value" as any argument to have the actual value of the property passed as that argument.
This combined with the use of a [Custom Type Provider](type-providers.md) can be helpful if your property is backed by
a scalar value, but the setter expects an object. Here's an example of what that would look like:

```php
/**
  * @FixtureFaker("myCustomProvider", arguments={"$value"})```
  */
```

You can also use `\$value` if you need to use the actual string `$value` as an argument. If you need to use `\$value` as
 an argument... you're out of luck. Pull request? :)

Previous chapter: [Advanced Usage](advanced-usage.md)<br />
Next chapter: [Custom Type Providers](type-providers.md)
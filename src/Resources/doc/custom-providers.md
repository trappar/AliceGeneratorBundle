# Custom Providers

There are cases where Doctrine returns non-scalar types directly from the database. For example, the Doctrine datetime
type returns a \DateTime object. In these cases the data from the entity can't be used directly to create a fixture, and
Alice can't know how to turn whatever data would be in the fixture directly into the correct data type.

You have probably already read about one solution this bundle offers for this problem in the section about the
[Faker Annotation](annotations.md#faker-annotation), but this solution can be a bit tedious when you
have an object type returned from many places in your database, and you would like it to be handled the same everywhere. 

This is why this bundle offers a custom providers feature, which will be very familiar to anyone who's used
AliceBundle's Custom Faker Providers.

*Side note: Since `DateTime` objects appear in entities quite often, we include a custom provider for it in this bundle.
You can take a look at the code for that [provider here](/src/DataFixtures/Faker/Provider/SpecificDateTimeProvider.php),
and its corresponding [service declaration here](/src/Resources/config/services.yml).*

## Example

For the following example we'll create a custom provider that covers converting to and from the "phone-number" doctrine
type provided by the [misd-service-development/phone-number-bundle](https://github.com/misd-service-development/phone-number-bundle).

```php
<?php

namespace AppBundle\DataFixtures\Faker\Provider;

use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Trappar\AliceGeneratorBundle\Annotation as Fixture;

class PhoneNumberProvider
{
    // Converts a international format phone number into a PhoneNumber object
    public static function phoneNumber($phoneNumber)
    {
        return PhoneNumberUtil::getInstance()->parse($phoneNumber, 'US')
    }
    
    // Converts a PhoneNumber object into a fixture-friendly format
    public static function toFixture(PhoneNumber $phoneNumber) {
        $number = PhoneNumberUtil::getInstance()->format($phoneNumber, PhoneNumberFormat::E164);
        return "<phoneNumber('$number')>";
    }
    
    /**
     * Alternative implementation of this method returning an array - this will yield the same result
     * @Fixture\Faker("phoneNumber")
     */
    public static function toFixture(PhoneNumber $phoneNumber) {
        $number = PhoneNumberUtil::getInstance()->format($phoneNumber, PhoneNumberFormat::E164);
        return [$number];
    }
}
```

The method signature of `toFixture` is very important here. Here are the important parts:

* Method may or may not be static (depending on if you need dependency injection)
* Named `toFixture`
* Must accept exactly one argument with a type declaration of the object type which this method supports.
* Must return either a string representation of the desired faker provider or an array of arguments for the provider.
* When returning an array of arguments, the method must have a @Faker annotation specifying the provider name.

Also, you can't have multiple providers which support the same object type - in that case you should use a 
[Faker annotation](src/Resources/doc/annotations.md#faker-annotation) instead.

Once you have this class/method setup, declare it as a service with the `trappar_alice_generator.faker.provider` and
`hautelook_alice.faker.provider` tags:

```yaml
# app/config/services.yml

services:
    faker.provider.phone_number:
        class: AppBundle\DataFixtures\Faker\Provider\PhoneNumberProvider
        tags: 
            - { name: trappar_alice_generator.faker.provider }
            - { name: hautelook_alice.faker.provider }
```

That's it! Now any time a PhoneNumber object is encountered in one of your Entities it will be handled nicely when
generating fixtures, and the Faker provider will handle converting the fixture back into the proper object!

Previous chapter: [Annotations](annotations.md)<br />
Next chapter: [Resources](../../../README.md#resources)
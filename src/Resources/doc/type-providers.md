# Custom Type Providers

There are cases where Doctrine returns non-scalar types directly from the database. For example, the Doctrine datetime type
returns a \DateTime object. In these cases the data from the entity can't be used directly to create a fixture, and Alice
can't know how to turn whatever data would be in the fixture directly into the correct data type. This bundle offers a solution
to this problem which will be very familiar to anyone who's used AliceBundle's Custom Faker Providers.

For the following example we'll create a custom Faker provider and type provider for the "phone-number" doctrine type provided by the 
[misd-service-development/phone-number-bundle](https://github.com/misd-service-development/phone-number-bundle).

```php
<?php

namespace AppBundle\DataFixtures\Faker\Provider;

use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

class PhoneNumberProvider
{
    // Converts a international format phone number into a PhoneNumber object
    public static function phoneNumber($phoneNumber)
    {
        return PhoneNumberUtil::getInstance()->parse($phoneNumber, 'US')
    }
    
    // Converts a PhoneNumber object into a fixture-friendly format
    public static function fixture(PhoneNumber $phoneNumber) {
        $number = PhoneNumberUtil::getInstance()->format($phoneNumber, PhoneNumberFormat::E164);
        return "<phoneNumber('$number')>";
    }
}
```

**Note**: Typically methods in type providers will be static, but they don't have to be.
If the method isn't static then you can use dependency injection just like with any other service.

Then declare it as a service with the `trappar_alice_generator.faker.provider` and `hautelook_alice.faker.provider` tags:

```yaml
# app/config/services.yml

services:
    faker.provider.phone_number:
        class: AppBundle\DataFixtures\Faker\Provider\PhoneNumberProvider
        tags: 
            - { name: trappar_alice_generator.faker.provider }
            - { name: hautelook_alice.faker.provider }
```

That's it! Now any time a PhoneNumber object is encountered in one of your Entities it will be handled nicely when generating fixtures, and
the Faker provider will handle converting the fixture back into the proper object!

Previous chapter: [Advanced Usage](advanced-usage.md)<br />
Next chapter: [Resources](../../../README.md#resources)
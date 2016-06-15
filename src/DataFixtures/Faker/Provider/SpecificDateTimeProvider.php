<?php

namespace Trappar\AliceGeneratorBundle\DataFixtures\Faker\Provider;

class SpecificDateTimeProvider
{
    public static function toFixture(\DateTime $dateTime)
    {
        $formatted = $dateTime->format('Y-m-d H:i:s');

        if (strpos($formatted, ' 00:00:00') !== false) {
            $formatted = str_replace(' 00:00:00', '', $dateTime->format('Y-m-d H:i:s'));
            return sprintf(
                '<(new \DateTime("%s"))>',
                $formatted
            );
        } else {
            return sprintf(
                '<(new \DateTime("%s", new \DateTimeZone("%s")))>',
                str_replace(' 00:00:00', '', $dateTime->format('Y-m-d H:i:s')),
                $dateTime->getTimezone()->getName()
            );
        }
    }
}
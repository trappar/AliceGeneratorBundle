<?php

namespace Trappar\AliceGeneratorBundle\DataFixtures\Faker\Provider;

class DateTimeProvider
{
    public function fixture(\DateTime $dateTime)
    {
        $dateTime = "<(new \\DateTime('{$dateTime->format('Y-m-d H:i:s')}'))>";

        return str_replace(' 00:00:00', '', $dateTime);
    }
}
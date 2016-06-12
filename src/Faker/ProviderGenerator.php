<?php

namespace Trappar\AliceGeneratorBundle\Faker;

class ProviderGenerator
{
    public static function generate($fakerName, $arguments)
    {
        $arguments = array_map(function ($item) {
            if (is_string($item)) {
                return '"' . $item . '"';
            } elseif (is_bool($item)) {
                return ($item) ? 'true' : 'false';
            }

            return $item;
        }, $arguments);

        $arguments = implode($arguments, ', ');

        return "<$fakerName($arguments)>";
    }
}
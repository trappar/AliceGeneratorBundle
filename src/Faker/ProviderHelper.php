<?php

namespace Trappar\AliceGeneratorBundle\Faker;

class ProviderHelper
{
    public static function generate($fakerName, $arguments)
    {
        $arguments = array_map(function ($item) {
            switch (gettype($item)) {
                case 'string':
                    return '"' . $item . '"';
                case 'boolean':
                    return ($item) ? 'true' : 'false';
                case 'NULL':
                    return 'null';
                default:
                    return $item;

            }
        }, $arguments);

        $arguments = implode($arguments, ', ');

        return "<$fakerName($arguments)>";
    }

    public static function call($target, $method, $possibleArgs)
    {
        $class = is_object($target) ? get_class($target) : $target;

        $reflectionMethod = new \ReflectionMethod($class, $method);
        $paramCount = count($reflectionMethod->getParameters());
        $possibleArgsCount = count($possibleArgs);

        if ($paramCount > $possibleArgsCount) {
            throw new \BadMethodCallException(sprintf(
                'Error while attempting to call class "%s" method "%s", this method may accept a maximum of %s arguments.',
                $class,
                $method,
                $possibleArgsCount
            ));
        }

        // Call their method with the number of arguments they are asking for
        return call_user_func_array([$target, $method], array_slice($possibleArgs, 0, $paramCount));
    }
}
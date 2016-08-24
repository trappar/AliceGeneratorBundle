<?php

namespace Trappar\AliceGeneratorBundle\Command;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Component\Yaml\Yaml;

class Validators
{
    /**
     * This provides a way to pass multiple arguments to a validator which would normally only be passed the single
     * value argument.
     *
     * @param       $methodName
     * @param mixed ...$boundArgs
     * @return \Closure
     */
    public static function createBoundValidator($methodName, ...$boundArgs)
    {
        return function(...$args) use ($methodName, $boundArgs) {
            return call_user_func_array("self::$methodName", array_merge($boundArgs, $args));
        };
    }

    public static function validateEntity(EntityManager $em, $entityAlias)
    {
        $metadata = null;

        if (strlen($entityAlias)) {
            try {
                $metadata = $em->getClassMetadata($entityAlias);
            } catch (\Exception $e) {
                throw new \InvalidArgumentException(sprintf(
                    'Unable to fetch entity information for "%s"',
                    $entityAlias
                ));
            }
        }

        return [$entityAlias, $metadata];
    }

    public static function validateSelectionType(array $selectionTypes, $value)
    {
        if (!in_array($value, $selectionTypes)) {
            throw new \InvalidArgumentException(sprintf('Invalid selection type "%s".', $value));
        }

        return $value;
    }

    public static function validateInt($value)
    {
        if (false === $result = filter_var($value, FILTER_VALIDATE_INT)) {
            throw new \InvalidArgumentException(sprintf('Invalid non-int given: %s.', $value));
        }

        return $result;
    }

    public static function validateID($value)
    {
        $ids = preg_split('~\s*,\s*~', $value);

        foreach ($ids as $key => $id) {
            if ($id === '') {
                unset($ids[$key]);
                continue;
            }

            $ids[$key] = self::validateInt($id);
        }

        return array_values($ids);
    }

    public static function validateWhereConditions(ClassMetadata $metadata, $value)
    {
        // Ensure surrounding brackets exist
        $value = preg_replace('~^\s*\{?~', '{', $value, 1);
        $value = preg_replace('~\}?\s*$~', '}', $value, 1);

        $parsed = Yaml::parse($value);

        if (!count($parsed)) {
            throw new \InvalidArgumentException('You must include at least one condition.');
        }

        $knownFields = $metadata->getFieldNames();

        foreach ($parsed as $field => $value) {
            if (!in_array($field, $knownFields)) {
                throw new \InvalidArgumentException(sprintf(
                    'Unknown field "%s".',
                    $field
                ));
            }
        }

        return $parsed;
    }

    public static function validateYesNo($value)
    {
        switch ($value) {
            case 'y':
            case 'yes':
                return true;
                break;
            case 'n':
            case 'no':
                return false;
                break;
            default:
                throw new \InvalidArgumentException('Must be either "yes" or "no".');
        }
    }

    public static function validateOutputPath($path)
    {
        $ext = pathinfo($path, PATHINFO_EXTENSION);

        if ($ext != 'yml') {
            throw new \InvalidArgumentException('Output file must have .yml extension.');
        }

        return $path;
    }
}
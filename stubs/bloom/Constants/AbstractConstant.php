<?php

declare(strict_types=1);

namespace Bloom\bloom\Constants;

use InvalidArgumentException;
use ReflectionClass;

abstract class AbstractConstant
{
    /**
     * Get a constant value by name
     *
     * @param  string  $name  The name of the constant
     * @return mixed The value of the constant
     *
     * @throws InvalidArgumentException
     */
    public static function getByName($name)
    {
        $constants = self::getConstants();

        if (! isset($constants[$name])) {
            throw new InvalidArgumentException(
                'Wrong '.self::getReflection()->getShortName().' name: '.$name
            );
        }

        return $constants[$name];
    }

    /**
     * Get a constant name by value
     *
     * @param  string  $value
     * @return int|string
     *
     * @throws InvalidArgumentException
     */
    public static function getByValue($value)
    {
        $constants = self::getConstants();
        foreach ($constants as $constantName => $constantValue) {
            if ($constantValue === $value) {
                return $constantName;
            }
        }
        throw new InvalidArgumentException(
            'Wrong '.self::getReflection()->getShortName().' value: '.$value
        );
    }

    /**
     * Get an array of all constants defined and their values
     */
    public static function getConstants(): array
    {
        return self::getReflection()->getConstants();
    }

    /**
     * Get an array of all constant names
     */
    public static function getConstantNames(): array
    {
        return array_keys(self::getConstants());
    }

    protected static function getReflection(): ReflectionClass
    {
        return new ReflectionClass(get_called_class());
    }
}

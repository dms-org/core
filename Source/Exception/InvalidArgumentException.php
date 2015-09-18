<?php

namespace Iddigital\Cms\Core\Exception;

/**
 * Exception for an invalid argument.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
class InvalidArgumentException extends BaseException
{
    private static function messageFormat($method, $argumentName, $extra = '')
    {
        return "Invalid argument $$argumentName supplied to $method: $extra";
    }

    /**
     * Verifies the supplied argument value is not null.
     *
     * @param string $method The method
     * @param string $name
     * @param mixed  $argument
     *
     * @return void
     * @throws static
     */
    public static function verifyNotNull($method, $name, $argument)
    {
        static::verify($argument !== null,
            self::messageFormat($method, $name, 'cannot be null'));
    }

    /**
     * Verifies the supplied argument value is an instance of the supplied class.
     *
     * @param string $method The method
     * @param string $name
     * @param mixed  $argument
     * @param string  $class
     *
     * @throws static
     */
    public static function verifyInstanceOf($method, $name, $argument, $class)
    {
        static::verify($argument instanceof $class,
            self::messageFormat($method, $name, 'expecting instance of %s, %s given'),
            $class,
            self::getType($argument));
    }

    /**
     * Verifies the supplied argument value is an array containing only
     * instances of the supplied class.
     *
     * @param string             $method The method
     * @param string             $name
     * @param array|\Traversable $argument
     * @param string             $class
     *
     * @throws mixed
     */
    public static function verifyAllInstanceOf($method, $name, $argument, $class)
    {
        $failed   = false;
        $failType = null;

        foreach ($argument as $value) {
            if (!($value instanceof $class)) {
                $failed   = true;
                $failType = self::getType($value);
            }
        }

        static::verify(!$failed,
            self::messageFormat($method, $name, 'expecting array of %s, %s found'),
            $class,
            $failType);
    }
}

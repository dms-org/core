<?php

namespace Iddigital\Cms\Core\Model\Object;

/**
 * This class contains a configuration option on whether to perform
 * accessibility validation for properties on typed objects.
 * This is expensive as it uses debug_backtrace to determine
 * whether a property is accessible from the caller scope.
 * You can disable this validation when in a production
 * environment.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
final class TypedObjectAccessibilityAssertion
{
    /**
     * @var bool
     */
    private static $enabled = true;

    /**
     * Sets whether to enable typed object property accessibility
     * assertions.
     *
     * @param bool $flag
     *
     * @return void
     */
    public static function enable($flag = true)
    {
        self::$enabled = $flag;
    }

    /**
     * @return bool
     */
    public static function isEnabled()
    {
        return self::$enabled;
    }
}
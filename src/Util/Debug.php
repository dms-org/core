<?php declare(strict_types = 1);

namespace Dms\Core\Util;

/**
 * The debug utility class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Debug
{
    /**
     * @param array $array
     *
     * @return string
     */
    public static function formatValues(array $array) : string
    {
        return implode(', ', array_map(function ($i) {
            return var_export($i, true);
        }, $array));
    }

    /**
     * @param mixed $value
     *
     * @return string
     */
    public static function getType($value) : string
    {
        return is_object($value) ? get_class($value) : gettype($value);
    }
}
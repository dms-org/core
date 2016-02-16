<?php declare(strict_types = 1);

namespace Dms\Core\Util\Hashing;

use Pinq\Iterators\Common\Identity;

/**
 * The value hasher.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ValueHasher
{
    /**
     * Returns whether the supplied values are value-wise equal.
     *
     * @param mixed $value1
     * @param mixed $value2
     *
     * @return bool
     */
    public static function areEqual($value1, $value2) : bool
    {
        return $value1 === $value2 || self::hash($value1) === self::hash($value2);
    }

    /**
     * Gets a unique string for the supplied value.
     *
     * @param mixed $value
     *
     * @return string
     */
    public static function hash($value) : string
    {
        if (is_object($value)) {
            if ($value instanceof IHashable) {
                return get_class($value) . ':' . $value->getObjectHash();
            } else {
                return serialize($value);
            }
        }

        return Identity::hash($value);
    }
}
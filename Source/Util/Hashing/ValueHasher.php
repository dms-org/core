<?php

namespace Iddigital\Cms\Core\Util\Hashing;

use Pinq\Iterators\Common\Identity;

/**
 * The value hasher.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ValueHasher
{
    /**
     * Gets a unique string for the supplied value.
     *
     * @param mixed $value
     *
     * @return string
     */
    public static function hash($value)
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
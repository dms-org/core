<?php

namespace Iddigital\Cms\Core\Util\Hashing;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Util\Debug;

/**
 * The object hasher.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ObjectHasher
{
    /**
     * Gets a unique string for the supplied object.
     *
     * @param object $object
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public static function hash($object)
    {
        if (!is_object($object)) {
            throw InvalidArgumentException::format(
                    'Invalid argument passed to %s: expecting object, %s given',
                    __METHOD__, Debug::getType($object)
            );
        }

        if ($object instanceof IHashable) {
            return get_class($object) . ':' . $object->getObjectHash();
        }

        return serialize($object);
    }
}
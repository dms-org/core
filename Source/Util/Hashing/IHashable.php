<?php

namespace Dms\Core\Util\Hashing;

/**
 * The hashable interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IHashable
{
    /**
     * Gets a unique string representing the state of the object.
     *
     * If two objects should be considered equal this method must
     * return the same result.
     *
     * @return string
     */
    public function getObjectHash();
}
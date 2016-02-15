<?php declare(strict_types = 1);

namespace Dms\Core\Model;

use Dms\Core\Exception;

/**
 * The typed object interface.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface ITypedObject
{
    /**
     * Returns an associated array of the values indexed by the property names.
     *
     * This returns all properties regardless of accessibility.
     *
     * @return array
     */
    public function toArray() : array;

    /**
     * Sets the properties of the object.
     *
     * The property types and structure are NOT validated in any way
     * and as such this should only be used for restoring object
     * state from a persistence store which is in a valid state.
     *
     * @param array $properties
     *
     * @return void
     */
    public function hydrate(array $properties);
}

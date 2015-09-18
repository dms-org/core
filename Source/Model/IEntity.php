<?php

namespace Iddigital\Cms\Core\Model;

use Iddigital\Cms\Core\Exception;

/**
 * The entity interface.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface IEntity extends ITypedObject
{
    /**
     * Returns the entity's unique identifier.
     *
     * @return int|null
     */
    public function getId();

    /**
     * Returns whether the entity has an id.
     *
     * @return bool
     */
    public function hasId();

    /**
     * Sets the entity's unique identifier.
     *
     * @param int $id
     *
     * @return void
     * @throws Exception\InvalidOperationException If the id is already set.
     */
    public function setId($id);
}

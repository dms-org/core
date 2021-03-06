<?php declare(strict_types = 1);

namespace Dms\Core\Model;

use Dms\Core\Exception;

/**
 * The entity interface.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface IEntity extends ITypedObject
{
    const ID = 'id';

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
    public function hasId() : bool;

    /**
     * Sets the entity's unique identifier.
     *
     * @param int $id
     *
     * @return void
     * @throws Exception\InvalidOperationException If the id is already set.
     */
    public function setId(int $id);
}

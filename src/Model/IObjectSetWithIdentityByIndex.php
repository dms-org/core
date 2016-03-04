<?php declare(strict_types = 1);

namespace Dms\Core\Model;

use Dms\Core\Exception;

/**
 * The object set interface that identifies an object by their 'index' within the set.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface IObjectSetWithIdentityByIndex extends IIdentifiableObjectSet
{
    /**
     * Returns whether the object with the given id is within this collection.
     *
     * @param $index
     *
     * @return bool
     */
    public function has($index) : bool;

    /**
     * Returns whether the objects with the given ids are within this collection.
     *
     * @param int[] $indexes
     *
     * @return bool
     */
    public function hasAll(array $indexes) : bool;

    /**
     * Returns the object with the given id.
     *
     * @param $index
     *
     * @return ITypedObject
     * @throws ObjectNotFoundException
     */
    public function get($index);

    /**
     * Returns the objects with the given ids.
     *
     * @param int[] $indexes
     *
     * @return ITypedObject[]
     * @throws ObjectNotFoundException
     */
    public function getAllById(array $indexes) : array;

    /**
     * Returns the object with the given id or null if does not exist.
     *
     * @param $index
     *
     * @return ITypedObject|null
     */
    public function tryGet($index);

    /**
     * Returns the objects with the given ids.
     *
     * @param int[] $indexes
     *
     * @return ITypedObject[]
     */
    public function tryGetAll(array $indexes) : array;
}
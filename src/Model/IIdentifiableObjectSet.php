<?php declare(strict_types = 1);

namespace Dms\Core\Model;

use Dms\Core\Exception;

/**
 * The object set interface that is able to identify objects by a integer.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface IIdentifiableObjectSet extends IObjectSet
{
    /**
     * Returns a id which is unique within the object set for the supplied object
     *
     * @param ITypedObject $object
     *
     * @return mixed
     * @throws Exception\InvalidArgumentException if the object is not within the collection
     */
    public function getObjectId(ITypedObject $object);

    /**
     * Returns whether the object with the given id is within this collection.
     *
     * @param $id
     *
     * @return bool
     */
    public function has($id) : bool;

    /**
     * Returns whether the objects with the given ids are within this collection.
     *
     * @param int[] $ids
     *
     * @return bool
     */
    public function hasAll(array $ids) : bool;

    /**
     * Returns the object with the given id.
     *
     * @param $id
     *
     * @return ITypedObject
     * @throws ObjectNotFoundException
     */
    public function get($id);

    /**
     * Returns the objects with the given ids.
     *
     * @param int[] $ids
     *
     * @return ITypedObject[]
     * @throws ObjectNotFoundException
     */
    public function getAllById(array $ids) : array;

    /**
     * Returns the object with the given id or null if does not exist.
     *
     * @param $id
     *
     * @return ITypedObject|null
     */
    public function tryGet($id);

    /**
     * Returns the objects with the given ids.
     *
     * @param int[] $ids
     *
     * @return ITypedObject[]
     */
    public function tryGetAll(array $ids) : array;
}
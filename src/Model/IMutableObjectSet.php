<?php declare(strict_types = 1);

namespace Dms\Core\Model;

use Dms\Core\Exception;

/**
 * The object set interface that is able to identify objects by a integer
 * and hence mutate them via their ids.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface IMutableObjectSet extends IIdentifiableObjectSet
{
    /**
     * Saves the supplied object to the object set.
     *
     * @param ITypedObject $object
     * @return void
     */
    public function save(ITypedObject $object);

    /**
     * Saves the supplied objects to the object set.
     *
     * @param ITypedObject[] $objects
     * @return void
     */
    public function saveAll(array $objects);

    /**
     * Removes the supplied object from the object set.
     *
     * @param ITypedObject $object
     * @return void
     */
    public function remove($object);

    /**
     * Removes the object with the supplied id from the object set.
     *
     * @param $id
     * @return void
     */
    public function removeById($id);

    /**
     * Removes the supplied objects from the object set.
     *
     * @param ITypedObject[] $objects
     * @return void
     */
    public function removeAll(array $objects);

    /**
     * Removes the objects with the supplied ids from the object set.
     *
     * @param int[] $ids
     * @return void
     */
    public function removeAllById(array $ids);

    /**
     * Removes all the objects from the object set.
     *
     * @return void
     */
    public function clear();

    /**
     * Removes all the objects matching the supplied criteria the object set.
     *
     * @param ICriteria $criteria
     *
     * @return void
     */
    public function removeMatching(ICriteria $criteria);

    /**
     * Returns an equivalent object set which represents a subset of the
     * initial object set.
     *
     * @param ICriteria $criteria
     *
     * @return IMutableObjectSet
     */
    public function subset(ICriteria $criteria) : IObjectSet;
}
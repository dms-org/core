<?php declare(strict_types = 1);

namespace Dms\Core\Model;

use Dms\Core\Exception;
use Dms\Core\Model\Criteria\Criteria;

/**
 * The object set interface.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface IObjectSet extends ITypedCollection, \Countable
{
    /**
     * Returns the object type of the object set.
     *
     * @return string
     */
    public function getObjectType() : string;

    /**
     * Returns a new criteria instance for the object type.
     *
     * @return Criteria
     */
    public function criteria() : Criteria;

    /**
     * Returns all the object as an array.
     *
     * @return ITypedObject[]
     */
    public function getAll() : array;

    /**
     * Returns the amount of objects
     *
     * @return int
     */
    public function count();

    /**
     * Returns whether the supplied object is contained within this set.
     *
     * @param ITypedObject $object
     *
     * @return bool
     * @throws Exception\TypeMismatchException
     */
    public function contains($object);

    /**
     * Returns whether the supplied objects are all contained within this set.
     *
     * @param ITypedObject[] $objects
     *
     * @return bool
     * @throws Exception\TypeMismatchException
     */
    public function containsAll(array $objects) : bool;

    /**
     * Returns the amount of objects matching the supplied criteria
     *
     * @param ICriteria $criteria
     *
     * @return int
     */
    public function countMatching(ICriteria $criteria) : int;

    /**
     * Returns an array of objects matching the supplied criteria.
     *
     * @param ICriteria $criteria
     *
     * @return ITypedObject[]
     * @throws Exception\TypeMismatchException
     */
    public function matching(ICriteria $criteria) : array;

    /**
     * Returns an array of objects which satisfy the supplied specification.
     *
     * @param ISpecification $specification
     *
     * @return ITypedObject[]
     * @throws Exception\TypeMismatchException
     */
    public function satisfying(ISpecification $specification) : array;

    /**
     * Returns an equivalent object set which represents a subset of the
     * initial object set.
     *
     * @param ICriteria $criteria
     *
     * @return IObjectSet
     */
    public function subset(ICriteria $criteria) : IObjectSet;
}
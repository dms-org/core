<?php declare(strict_types = 1);

namespace Dms\Core\Model;

use Dms\Core\Exception;
use Dms\Core\Model\Criteria\Condition\Condition;
use Dms\Core\Model\Criteria\Criteria;
use Dms\Core\Model\Criteria\MemberOrdering;
use Dms\Core\Model\Object\FinalizedClassDefinition;

/**
 * The object search criteria interface.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface ICriteria
{
    /**
     * Returns the object type of the criteria.
     *
     * @return FinalizedClassDefinition
     */
    public function getClass() : Object\FinalizedClassDefinition;

    /**
     * Throws an exception if the criteria of not for the supplied class.
     *
     * @param string $class
     *
     * @return void
     * @throws Exception\TypeMismatchException
     */
    public function verifyOfClass(string $class);

    /**
     * Returns whether the criteria has a condition.
     *
     * @return bool
     */
    public function hasCondition() : bool;

    /**
     * Gets the condition defining which objects to load.
     *
     * @return Condition|null
     */
    public function getCondition();

    /**
     * Gets whether the criteria contains any orderings.
     *
     * @return bool
     */
    public function hasOrderings() : bool;

    /**
     * Gets the orderings of the object.
     *
     * @return MemberOrdering[]
     */
    public function getOrderings() : array;

    /**
     * Gets the amount of objects to skip
     *
     * @return int
     */
    public function getStartOffset() : int;

    /**
     * Returns whether the criteria has a limit set.
     *
     * @return bool
     */
    public function hasLimitAmount() : bool;

    /**
     * Gets the maximum amount of objects to return or null
     * if no limit is set.
     *
     * @return int|null
     */
    public function getLimitAmount();

    /**
     * Merges the criteria.
     *
     * @param ICriteria $criteria
     *
     * @return ICriteria
     */
    public function merge(ICriteria $criteria) : ICriteria;

    /**
     * Returns a copy of the criteria which can be modified.
     * 
     * @return Criteria
     */
    public function asMutableCriteria() : Criteria;
}

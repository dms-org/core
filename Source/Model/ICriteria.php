<?php

namespace Iddigital\Cms\Core\Model;

use Iddigital\Cms\Core\Exception;
use Iddigital\Cms\Core\Model\Criteria\Condition\Condition;
use Iddigital\Cms\Core\Model\Criteria\MemberOrdering;
use Iddigital\Cms\Core\Model\Object\FinalizedClassDefinition;

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
    public function getClass();

    /**
     * Throws an exception if the criteria of not for the supplied class.
     *
     * @param string $class
     *
     * @return void
     * @throws Exception\TypeMismatchException
     */
    public function verifyOfClass($class);

    /**
     * Returns whether the criteria has a condition.
     *
     * @return bool
     */
    public function hasCondition();

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
    public function hasOrderings();

    /**
     * Gets the orderings of the object.
     *
     * @return MemberOrdering[]
     */
    public function getOrderings();

    /**
     * Gets the amount of objects to skip
     *
     * @return int
     */
    public function getStartOffset();

    /**
     * Returns whether the criteria has a limit set.
     *
     * @return bool
     */
    public function hasLimitAmount();

    /**
     * Gets the maximum amount of objects to return or null
     * if no limit is set.
     *
     * @return int|null
     */
    public function getLimitAmount();
}

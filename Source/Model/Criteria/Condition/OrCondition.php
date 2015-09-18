<?php

namespace Iddigital\Cms\Core\Model\Criteria\Condition;

use Iddigital\Cms\Core\Model\ITypedObject;

/**
 * The logical or condition class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class OrCondition extends CompositeCondition
{
    /**
     * Returns a callable that takes an object and returns a boolean
     * whether the object passes the condition.
     *
     * @return callable
     */
    protected function makeFilterCallable()
    {
        $conditions = $this->conditions;
        /** @var Condition $firstCondition */
        $firstCondition = array_shift($conditions);
        $filter = $firstCondition->getFilterCallable();

        foreach ($conditions as $condition) {
            $innerFilter = $condition->getFilterCallable();

            $filter = function (ITypedObject $object) use ($innerFilter, $filter) {
                return $innerFilter($object) || $filter($object);
            };
        }

        return $filter;
    }
}
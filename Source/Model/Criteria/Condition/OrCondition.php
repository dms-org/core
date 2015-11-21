<?php

namespace Iddigital\Cms\Core\Model\Criteria\Condition;

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
    protected function makeArrayFilterCallable()
    {
        $conditions = $this->getConditions();
        /** @var Condition $firstCondition */
        $firstCondition = array_shift($conditions);
        $filter         = $firstCondition->getArrayFilterCallable();

        foreach ($conditions as $condition) {
            $innerFilter = $condition->getArrayFilterCallable();

            $filter = function (array $objects) use ($innerFilter, $filter) {
                return $innerFilter($objects) + $filter($objects);
            };
        }

        return $filter;
    }
}
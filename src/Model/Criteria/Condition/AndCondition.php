<?php declare(strict_types = 1);

namespace Dms\Core\Model\Criteria\Condition;

/**
 * The logical and condition class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class AndCondition extends CompositeCondition
{
    /**
     * @inheritdoc
     */
    protected function makeArrayFilterCallable() : callable
    {
        $conditions = $this->getConditions();
        /** @var Condition $firstCondition */
        $firstCondition = array_shift($conditions);
        $filter         = $firstCondition->getArrayFilterCallable();

        foreach ($conditions as $condition) {
            $innerFilter = $condition->getArrayFilterCallable();

            $filter = function (array $objects) use ($innerFilter, $filter) {
                return array_intersect_key($innerFilter($objects), $filter($objects));
            };
        }

        return $filter;
    }
}
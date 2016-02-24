<?php declare(strict_types = 1);

namespace Dms\Core\Table\Criteria;

use Dms\Core\Exception;
use Dms\Core\Model\Criteria\Condition\ConditionOperator;
use Dms\Core\Table\IColumn;
use Dms\Core\Table\IColumnComponent;
use Dms\Core\Table\IColumnComponentOperator;
use Dms\Core\Table\IRowCriteria;
use Dms\Core\Util\Debug;

/**
 * The column condition group class
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
class ColumnConditionGroup
{
    /**
     * @var string
     */
    protected $conditionMode;

    /**
     * @var ColumnCondition[]
     */
    protected $conditions;

    /**
     * ColumnConditionGroup constructor.
     *
     * @param string            $conditionMode
     * @param ColumnCondition[] $conditions
     *
     * @throws Exception\InvalidArgumentException
     */
    public function __construct(string $conditionMode, array $conditions)
    {
        if (!in_array($conditionMode, [IRowCriteria::CONDITION_MODE_AND, IRowCriteria::CONDITION_MODE_OR], true)) {
            throw Exception\InvalidArgumentException::format(
                'Invalid condition mode supplied to %s: expecting one of (%s), \'%s\' given',
                __METHOD__, Debug::formatValues([IRowCriteria::CONDITION_MODE_AND, IRowCriteria::CONDITION_MODE_OR]), $conditionMode
            );
        }

        Exception\InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'conditions', $conditions, ColumnCondition::class);

        $this->conditionMode = $conditionMode;
        $this->conditions    = $conditions;
    }

    /**
     * @return string
     */
    public function getConditionMode()
    {
        return $this->conditionMode;
    }

    /**
     * @return ColumnCondition[]
     */
    public function getConditions()
    {
        return $this->conditions;
    }
}

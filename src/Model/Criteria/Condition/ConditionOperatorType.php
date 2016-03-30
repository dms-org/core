<?php declare(strict_types = 1);

namespace Dms\Core\Model\Criteria\Condition;

use Dms\Core\Model\Type\IType;

/**
 * The property condition operator type.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ConditionOperatorType
{
    /**
     * @var string
     */
    protected $operator;

    /**
     * @var IType
     */
    protected $valueType;

    /**
     * ConditionOperatorType constructor.
     *
     * @param string $operator
     * @param IType  $valueType
     */
    public function __construct(string $operator, IType $valueType)
    {
        ConditionOperator::validate($operator);
        $this->operator  = $operator;
        $this->valueType = $valueType;
    }

    /**
     * @return string
     */
    final public function getOperator() : string
    {
        return $this->operator;
    }

    /**
     * @return IType
     */
    final public function getValueType() : IType
    {
        return $this->valueType;
    }
}
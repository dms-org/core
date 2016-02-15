<?php declare(strict_types = 1);

namespace Dms\Core\Model\Type;

use Dms\Core\Model\Criteria\Condition\ConditionOperator;
use Dms\Core\Model\Type\Builder\Type;

/**
 * The null type class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class NullType extends BaseType
{
    public function __construct()
    {
        parent::__construct('null');
    }

    /**
     * @param IType $type
     *
     * @return IType|null
     */
    protected function intersection(IType $type)
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    protected function loadValidOperatorTypes() : array
    {
        $mixedType      = Type::mixed();
        $array          = Type::arrayOf($mixedType);
        $nullableString = Type::string()->nullable();

        return [
                ConditionOperator::EQUALS                           => $mixedType,
                ConditionOperator::NOT_EQUALS                       => $mixedType,
                ConditionOperator::IN                               => $array,
                ConditionOperator::NOT_IN                           => $array,
                ConditionOperator::STRING_CONTAINS                  => $nullableString,
                ConditionOperator::STRING_CONTAINS_CASE_INSENSITIVE => $nullableString,
                ConditionOperator::GREATER_THAN                     => $mixedType,
                ConditionOperator::GREATER_THAN_OR_EQUAL            => $mixedType,
                ConditionOperator::LESS_THAN                        => $mixedType,
                ConditionOperator::LESS_THAN_OR_EQUAL               => $mixedType,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function isOfType($value) : bool
    {
        return $value === null;
    }
}
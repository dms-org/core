<?php declare(strict_types = 1);

namespace Dms\Core\Table\Column\Component\Type;

use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Form\IField;
use Dms\Core\Model\Criteria\Condition\ConditionOperator;
use Dms\Core\Table\IColumnComponentOperator;

/**
 * The standard operators helper class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class StandardConditions
{

    /**
     * @param IField   $field
     * @param string[] $operators
     *
     * @return IColumnComponentOperator[]
     */
    public static function forField(IField $field, array $operators = null) : array
    {
        if ($operators === null) {
            $operators = $field->getProcessedType()->getConditionOperators();
        }

        $conditions = [];

        $arrayField = Field::name($field->getName())
                ->label($field->getLabel())
                ->arrayOfField($field)
                ->build();

        $stringField = Field::name($field->getName())
                ->label($field->getLabel())
                ->string()
                ->build();

        foreach ($operators as $operator) {
            if ($operator === ConditionOperator::IN || $operator === ConditionOperator::NOT_IN) {
                $conditions[] = new ColumnComponentOperator($operator, $arrayField);
            } elseif ($operator === ConditionOperator::STRING_CONTAINS || $operator === ConditionOperator::STRING_CONTAINS_CASE_INSENSITIVE) {
                $conditions[] = new ColumnComponentOperator($operator, $stringField);
            } else {
                $conditions[] = new ColumnComponentOperator($operator, $field);
            }
        }

        return $conditions;
    }

}
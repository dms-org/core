<?php

namespace Iddigital\Cms\Core\Table\Column\Component\Type;

use Iddigital\Cms\Core\Form\Field\Builder\Field;
use Iddigital\Cms\Core\Form\IField;
use Iddigital\Cms\Core\Model\Criteria\Condition\ConditionOperator;
use Iddigital\Cms\Core\Table\IColumnComponentOperator;

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
    public static function forField(IField $field, array $operators = null)
    {
        if ($operators === null) {
            $operators = $field->getProcessedType()->getConditionOperators();
        }

        $conditions = [];

        $arrayField = Field::name($field->getName())
                ->label($field->getLabel())
                ->arrayOfField($field)
                ->build();

        foreach ($operators as $operator) {
            if ($operator === ConditionOperator::IN || $operator === ConditionOperator::NOT_IN) {
                $conditions[] = new ColumnComponentOperator($operator, $arrayField);
            } else {
                $conditions[] = new ColumnComponentOperator($operator, $field);
            }
        }

        return $conditions;
    }

}
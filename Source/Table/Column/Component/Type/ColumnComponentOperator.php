<?php

namespace Iddigital\Cms\Core\Table\Column\Component\Type;

use Iddigital\Cms\Core\Form\IField;
use Iddigital\Cms\Core\Model\Criteria\Condition\ConditionOperator;
use Iddigital\Cms\Core\Table\IColumnComponentOperator;

/**
 * The column condition base class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ColumnComponentOperator implements IColumnComponentOperator
{
    /**
     * @var string
     */
    private $operator;

    /**
     * @var IField
     */
    private $field;

    /**
     * ColumnCondition constructor.
     *
     * @param string $operator
     * @param IField $field
     */
    public function __construct($operator, IField $field)
    {
        ConditionOperator::validate($operator);
        $this->operator = $operator;
        $this->field    = $field;
    }

    /**
     * @return string
     */
    final public function getOperator()
    {
        return $this->operator;
    }

    /**
     * @return IField
     */
    final public function getField()
    {
        return $this->field;
    }
}
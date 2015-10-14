<?php

namespace Iddigital\Cms\Core\Table\Column\Component\Type;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Form\IField;
use Iddigital\Cms\Core\Model\Type\IType;
use Iddigital\Cms\Core\Table\IColumnComponentOperator;
use Iddigital\Cms\Core\Table\IColumnComponentType;
use Iddigital\Cms\Core\Util\Debug;

/**
 * The column component type base class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ColumnComponentType implements IColumnComponentType
{
    /**
     * @var IType
     */
    protected $phpType;

    /**
     * @var IColumnComponentOperator[]
     */
    protected $validOperators = [];

    /**
     * ColumnType constructor.
     *
     * @param IType                      $phpType
     * @param IColumnComponentOperator[] $validOperators
     */
    public function __construct(IType $phpType, array $validOperators)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'validConditions', $validOperators, IColumnComponentOperator::class);

        $this->phpType = $phpType;
        foreach ($validOperators as $validOperator) {
            $this->validOperators[$validOperator->getOperator()] = $validOperator;
        }
    }

    /**
     * @param IField $field
     *
     * @return ColumnComponentType
     */
    public static function forField(IField $field)
    {
        return new self(
                $field->getProcessedType(),
                StandardConditions::forField($field, $field->getProcessedType()->getConditionOperators())
        );
    }

    /**
     * {@inheritDoc}
     */
    final public function getPhpType()
    {
        return $this->phpType;
    }

    /**
     * {@inheritDoc}
     */
    final public function getConditionOperators()
    {
        return $this->validOperators;
    }

    /**
     * @param string $operatorString
     *
     * @return bool
     */
    public function hasOperator($operatorString)
    {
        return isset($this->validOperators[$operatorString]);
    }

    /**
     * @param string $operatorString
     *
     * @return IColumnComponentOperator
     * @throws InvalidArgumentException
     */
    public function getOperator($operatorString)
    {
        if (!isset($this->validOperators[$operatorString])) {
            throw InvalidArgumentException::format(
                    'Invalid operator string for column component type for \'%s\': expecting one of (%s), %s given',
                    $this->phpType->asTypeString(), Debug::formatValues(array_keys($this->validOperators)), $operatorString
            );
        }

        return $this->validOperators[$operatorString];
    }

    /**
     * @inheritDoc
     */
    public function withFieldAs($name, $label)
    {
        $clone = clone $this;

        foreach ($clone->validOperators as $key => $operator) {
            $clone->validOperators[$key] = $operator->withFieldAs($name, $label);
        }

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function equals(IColumnComponentType $type)
    {
        return $this->withFieldAs('*', '*') == $type->withFieldAs('*', '*');
    }
}
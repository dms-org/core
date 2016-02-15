<?php declare(strict_types = 1);

namespace Dms\Core\Table\Column\Component\Type;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Form\IField;
use Dms\Core\Model\Type\IType;
use Dms\Core\Table\IColumnComponentOperator;
use Dms\Core\Table\IColumnComponentType;
use Dms\Core\Util\Debug;

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
    public static function forField(IField $field) : ColumnComponentType
    {
        return new self(
                $field->getProcessedType(),
                StandardConditions::forField($field, $field->getProcessedType()->getConditionOperators())
        );
    }

    /**
     * {@inheritDoc}
     */
    final public function getPhpType() : \Dms\Core\Model\Type\IType
    {
        return $this->phpType;
    }

    /**
     * {@inheritDoc}
     */
    final public function getConditionOperators() : array
    {
        return $this->validOperators;
    }

    /**
     * @param string $operatorString
     *
     * @return bool
     */
    public function hasOperator(string $operatorString) : bool
    {
        return isset($this->validOperators[$operatorString]);
    }

    /**
     * @param string $operatorString
     *
     * @return IColumnComponentOperator
     * @throws InvalidArgumentException
     */
    public function getOperator(string $operatorString) : \Dms\Core\Table\IColumnComponentOperator
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
    public function withFieldAs(string $name, string $label)
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
    public function equals(IColumnComponentType $type) : bool
    {
        return $this->withFieldAs('*', '*') == $type->withFieldAs('*', '*');
    }
}
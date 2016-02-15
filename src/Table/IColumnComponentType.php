<?php declare(strict_types = 1);

namespace Dms\Core\Table;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Model\Type\IType;

/**
 * The column component type interface
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IColumnComponentType
{
    /**
     * Gets the php type of the values within the column component.
     *
     * @return IType
     */
    public function getPhpType() : \Dms\Core\Model\Type\IType;

    /**
     * Gets the valid condition for the component type.
     *
     * @return IColumnComponentOperator[]
     */
    public function getConditionOperators() : array;

    /**
     * Returns whether the column component supports the operator.
     *
     * @see ConditionOperator constants
     *
     * @param string $operatorString
     *
     * @return bool
     */
    public function hasOperator(string $operatorString) : bool;

    /**
     * Gets the column condition operator or throws an exception if
     * the operator is not supported.
     *
     * @see ConditionOperator constants
     *
     * @param string $operatorString
     *
     * @return IColumnComponentOperator
     * @throws InvalidArgumentException
     */
    public function getOperator(string $operatorString) : IColumnComponentOperator;

    /**
     * Returns whether the types are equal
     *
     * @param IColumnComponentType $type
     *
     * @return bool
     */
    public function equals(IColumnComponentType $type) : bool;

    /**
     * Returns an equivalent type with the operator fields with the
     * supplied name and label.
     *
     * @param string $name
     * @param string $label
     *
     * @return static
     */
    public function withFieldAs(string $name, string $label);
}
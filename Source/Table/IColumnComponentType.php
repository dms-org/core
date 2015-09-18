<?php

namespace Iddigital\Cms\Core\Table;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Model\Type\IType;

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
    public function getPhpType();

    /**
     * Gets the valid condition for the component type.
     *
     * @return IColumnComponentOperator[]
     */
    public function getConditionOperators();

    /**
     * Returns whether the column component supports the operator.
     *
     * @see ConditionOperator constants
     *
     * @param string $operatorString
     *
     * @return bool
     */
    public function hasOperator($operatorString);

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
    public function getOperator($operatorString);
}
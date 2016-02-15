<?php declare(strict_types = 1);

namespace Dms\Core\Model\Criteria;

use Dms\Core\Exception\NotImplementedException;
use Dms\Core\Model\Object\FinalizedPropertyDefinition;
use Dms\Core\Model\Type\IType;

/**
 * The member expression interface
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IMemberExpression
{
    /**
     * Gets the expression in a string format.
     *
     * @return string
     */
    public function asString() : string;

    /**
     * Gets the source type of the expression.
     *
     * @return IType
     */
    public function getSourceType() : \Dms\Core\Model\Type\IType;

    /**
     * Gets the resulting type of the expression.
     *
     * @return IType
     */
    public function getResultingType() : \Dms\Core\Model\Type\IType;

    /**
     * Returns whether the resulting expression is a property value.
     *
     * @return bool
     */
    public function isPropertyValue() : bool;

    /**
     * Gets the property of which the expression returns.
     *
     * @return FinalizedPropertyDefinition|null
     */
    public function getProperty();

    /**
     * Returns a callable that takes a parameter of the source type
     * and returns the value of the object member.
     *
     * @return callable
     * @throws NotImplementedException
     */
    public function createGetterCallable() : callable;

    /**
     * Returns a callable that takes a parameter an array of the source types
     * and returns an array containing values of the object members.
     *
     * NOTE: array keys are maintained
     *
     * @return callable
     * @throws NotImplementedException
     */
    public function createArrayGetterCallable() : callable;
}
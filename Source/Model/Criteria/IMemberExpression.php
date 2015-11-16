<?php

namespace Iddigital\Cms\Core\Model\Criteria;

use Iddigital\Cms\Core\Exception\NotImplementedException;
use Iddigital\Cms\Core\Model\Object\FinalizedPropertyDefinition;
use Iddigital\Cms\Core\Model\Type\IType;

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
    public function asString();

    /**
     * Gets the source type of the expression.
     *
     * @return IType
     */
    public function getSourceType();

    /**
     * Gets the resulting type of the expression.
     *
     * @return IType
     */
    public function getResultingType();

    /**
     * Returns whether the resulting expression is a property value.
     *
     * @return bool
     */
    public function isPropertyValue();

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
    public function createGetterCallable();

    /**
     * Returns a callable that takes a parameter an array of the source types
     * and returns an array containing values of the object members.
     *
     * NOTE: array keys are maintained
     *
     * @return callable
     * @throws NotImplementedException
     */
    public function createArrayGetterCallable();
}
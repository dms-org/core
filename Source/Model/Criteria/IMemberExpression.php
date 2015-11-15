<?php

namespace Iddigital\Cms\Core\Model\Criteria;

use Iddigital\Cms\Core\Exception\NotImplementedException;
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
     * Returns a callable that takes a parameter of the
     * source
     *
     * @return callable
     * @throws NotImplementedException
     */
    public function createGetterCallable();
}
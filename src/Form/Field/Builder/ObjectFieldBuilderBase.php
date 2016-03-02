<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Builder;

/**
 * The object field builder base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class ObjectFieldBuilderBase extends FieldBuilderBase
{
    /**
     * Labels the object options with values of the supplied member expression.
     *
     * @param string $memberExpression
     *
     * @return static
     */
    abstract public function labelledBy(string $memberExpression);

    /**
     * Labels the object options with the returned values of the supplied callback.
     *
     * @param callable $labelCallback
     *
     * @return static
     */
    abstract public function labelledByCallback(callable $labelCallback);
}
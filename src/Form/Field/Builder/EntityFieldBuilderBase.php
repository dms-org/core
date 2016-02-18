<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Builder;

use Dms\Core\Form\Field\Options\EntityIdOptions;
use Dms\Core\Form\Field\Type\FieldType;

/**
 * The entity field builder base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class EntityFieldBuilderBase extends FieldBuilderBase
{
    /**
     * Labels the entity options with values of the supplied member expression.
     *
     * @param string $memberExpression
     *
     * @return static
     */
    abstract public function labelledBy(string $memberExpression);

    /**
     * Labels the entity options with the returned values of the supplied callback.
     *
     * @param callable $labelCallback
     *
     * @return static
     */
    abstract public function labelledByCallback(callable $labelCallback);
}
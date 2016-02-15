<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Builder;

use Dms\Core\Form\Field\Options\EntityIdOptions;
use Dms\Core\Form\Field\Type\FieldType;

/**
 * The entity field builder base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EntityFieldBuilderBase extends FieldBuilderBase
{
    /**
     * Labels the entity options with values of the supplied member expression.
     *
     * @param string $memberExpression
     *
     * @return static
     */
    public function labelledBy(string $memberExpression)
    {
        /** @var EntityIdOptions $options */
        $options = $this->type->get(FieldType::ATTR_OPTIONS);

        return $this->attr(FieldType::ATTR_OPTIONS, new EntityIdOptions(
                $options->getEntities(),
                null,
                $memberExpression
        ));
    }

    /**
     * Labels the entity options with the returned values of the supplied callback.
     *
     * @param callable $labelCallback
     *
     * @return static
     */
    public function labelledByCallback(callable $labelCallback)
    {
        /** @var EntityIdOptions $options */
        $options = $this->type->get(FieldType::ATTR_OPTIONS);

        return $this->attr(FieldType::ATTR_OPTIONS, new EntityIdOptions(
                $options->getEntities(),
                $labelCallback
        ));
    }
}
<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Builder;

use Dms\Core\Form\Field\Options\EntityIdOptions;
use Dms\Core\Form\Field\Type\ArrayOfEntityIdsType;
use Dms\Core\Form\Field\Type\ArrayOfType;
use Dms\Core\Form\IField;

/**
 * The entity array field builder class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EntityArrayFieldBuilder extends EntityFieldBuilderBase
{
    use ArrayFieldBuilderTrait;

    /**
     * Labels the entity options with values of the supplied member expression.
     *
     * @param string $memberExpression
     *
     * @return static
     */
    public function labelledBy(string $memberExpression)
    {
        /** @var ArrayOfEntityIdsType $fieldType */
        $fieldType       = $this->type;

        /** @var EntityIdOptions $options */
        $options         = $fieldType->getElementType()->getOptions();

        $entityFieldType = $fieldType->with(ArrayOfType::ATTR_OPTIONS, new EntityIdOptions(
            $options->getEntities(),
            null,
            $memberExpression
        ));

        $this->type = $fieldType->withElementFieldType($entityFieldType);

        return $this;
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
        /** @var ArrayOfEntityIdsType $fieldType */
        $fieldType       = $this->type;

        /** @var EntityIdOptions $options */
        $options         = $fieldType->getElementType()->getOptions();

        $entityFieldType = $fieldType->with(ArrayOfType::ATTR_OPTIONS, new EntityIdOptions(
            $options->getEntities(),
            $labelCallback,
            null
        ));

        $this->type = $fieldType->withElementFieldType($entityFieldType);

        return $this;
    }
}
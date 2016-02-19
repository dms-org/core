<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Builder;

use Dms\Core\Form\Field\Options\EntityIdOptions;
use Dms\Core\Form\Field\Type\ArrayOfEntityIdsType;
use Dms\Core\Form\Field\Type\ArrayOfType;

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
        $fieldType = $this->type;

        /** @var EntityIdOptions $options */
        $elementType = $fieldType->getElementType();
        $options     = $elementType->getOptions();

        $entityFieldType = $elementType->with(ArrayOfType::ATTR_OPTIONS, new EntityIdOptions(
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
        $fieldType = $this->type;

        /** @var EntityIdOptions $options */
        $elementType = $fieldType->getElementType();
        $options     = $elementType->getOptions();

        $entityFieldType = $elementType->with(ArrayOfType::ATTR_OPTIONS, new EntityIdOptions(
            $options->getEntities(),
            $labelCallback,
            null
        ));

        $this->type = $fieldType->withElementFieldType($entityFieldType);

        return $this;
    }
}
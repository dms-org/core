<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Builder;

use Dms\Core\Form\Field\Options\ObjectIndexOptions;
use Dms\Core\Form\Field\Type\ArrayOfObjectIdsType;
use Dms\Core\Form\Field\Type\ArrayOfType;

/**
 * The object array field builder class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ObjectArrayFieldBuilder extends ObjectFieldBuilderBase
{
    use ArrayFieldBuilderTrait;

    /**
     * @inheritdoc
     */
    public function labelledBy(string $memberExpression)
    {
        /** @var ArrayOfObjectIdsType $fieldType */
        $fieldType = $this->type;

        /** @var ObjectIndexOptions $options */
        $elementType = $fieldType->getElementType();
        $options     = $elementType->getOptions();

        $objectIdFieldType = $elementType->with(ArrayOfType::ATTR_OPTIONS, $options->withLabelMemberExpression($memberExpression));

        $this->type = $fieldType->withElementFieldType($objectIdFieldType);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function labelledByCallback(callable $labelCallback)
    {
        /** @var ArrayOfObjectIdsType $fieldType */
        $fieldType = $this->type;

        /** @var ObjectIndexOptions $options */
        $elementType = $fieldType->getElementType();
        $options     = $elementType->getOptions();

        $objectIdFieldType = $elementType->with(ArrayOfType::ATTR_OPTIONS, $options->withLabelCallback($labelCallback));

        $this->type = $fieldType->withElementFieldType($objectIdFieldType);

        return $this;
    }
}
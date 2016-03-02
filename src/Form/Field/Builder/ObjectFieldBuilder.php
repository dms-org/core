<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Builder;

use Dms\Core\Form\Field\Options\ObjectIdentityOptions;
use Dms\Core\Form\Field\Type\FieldType;

/**
 * The object field builder class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ObjectFieldBuilder extends ObjectFieldBuilderBase
{
    /**
     * @inheritdoc
     */
    public function labelledBy(string $memberExpression)
    {
        /** @var ObjectIdentityOptions $options */
        $options = $this->type->get(FieldType::ATTR_OPTIONS);

        return $this->attr(FieldType::ATTR_OPTIONS, $options->withLabelMemberExpression($memberExpression));
    }

    /**
     * @inheritdoc
     */
    public function labelledByCallback(callable $labelCallback)
    {
        /** @var ObjectIdentityOptions $options */
        $options = $this->type->get(FieldType::ATTR_OPTIONS);

        return $this->attr(FieldType::ATTR_OPTIONS, $options->withLabelCallback($labelCallback));
    }
}
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
     * @param callable $callback
     */
    protected function updateOptions(callable $callback)
    {
        /** @var ObjectIdentityOptions $options */
        $options = $this->attributes[FieldType::ATTR_OPTIONS] ?? $this->type->get(FieldType::ATTR_OPTIONS);

        $this->attr(FieldType::ATTR_OPTIONS, $callback($options));
    }
}
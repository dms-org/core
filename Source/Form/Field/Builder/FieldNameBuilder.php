<?php

namespace Iddigital\Cms\Core\Form\Field\Builder;

/**
 * The field name builder class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FieldNameBuilder extends FieldBuilderBase
{
    /**
     * @param $name
     *
     * @return FieldLabelBuilder
     */
    public function name($name)
    {
        $this->name = $name;

        return new FieldLabelBuilder($this);
    }
}
<?php

namespace Iddigital\Cms\Core\Form\Field\Builder;

/**
 * The field label builder class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FieldLabelBuilder extends FieldBuilderBase
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

    /**
     * @param string $label
     *
     * @return Field
     */
    public function label($label)
    {
        $this->label = $label;

        return new Field($this);
    }
}
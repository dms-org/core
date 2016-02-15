<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Builder;

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
    public function name($name) : FieldLabelBuilder
    {
        $this->name = $name;

        return new FieldLabelBuilder($this);
    }

    /**
     * @param string $label
     *
     * @return Field
     */
    public function label(string $label) : Field
    {
        $this->label = $label;

        return new Field($this);
    }
}
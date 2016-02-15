<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Builder;

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
    public function name($name) : FieldLabelBuilder
    {
        $this->name = $name;

        return new FieldLabelBuilder($this);
    }
}
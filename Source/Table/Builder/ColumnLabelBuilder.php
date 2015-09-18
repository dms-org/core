<?php

namespace Iddigital\Cms\Core\Table\Builder;

use Iddigital\Cms\Core\Table\IColumnComponent;

/**
 * The column name builder class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ColumnLabelBuilder extends ColumnBuilderBase
{
    /**
     * Defines the label of the column
     *
     * @param string $label
     *
     * @return ColumnComponentsBuilder
     */
    public function label($label)
    {
        $this->label = $label;

        return new ColumnComponentsBuilder($this);
    }
}
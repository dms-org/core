<?php declare(strict_types = 1);

namespace Dms\Core\Table\Builder;

use Dms\Core\Table\IColumnComponent;

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
    public function label(string $label) : ColumnComponentsBuilder
    {
        $this->label = $label;

        return new ColumnComponentsBuilder($this);
    }
}
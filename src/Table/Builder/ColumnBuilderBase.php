<?php declare(strict_types = 1);

namespace Dms\Core\Table\Builder;

use Dms\Core\Exception\InvalidOperationException;
use Dms\Core\Table\Column\Column;
use Dms\Core\Table\IColumn;
use Dms\Core\Table\IColumnComponent;

/**
 * The column builder base class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class ColumnBuilderBase
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var bool
     */
    protected $hidden = false;

    /**
     * @var IColumnComponent[]
     */
    protected $components = [];

    /**
     * ColumnBuilderBase constructor.
     *
     * @param ColumnBuilderBase $previous
     */
    final protected function __construct(ColumnBuilderBase $previous = null)
    {
        if ($previous) {
            $this->name       = $previous->name;
            $this->label      = $previous->label;
            $this->hidden     = $previous->hidden;
            $this->components = $previous->components;
        }
    }

    /**
     * Defines the column as hidden.
     *
     * @param bool $hidden
     *
     * @return static
     */
    public function hidden(bool $hidden = true)
    {
        $this->hidden = $hidden;

        return $this;
    }

    /**
     * @return IColumn
     * @throws InvalidOperationException
     */
    protected function build() : IColumn
    {
        if (!$this->name || !$this->label || !$this->components) {
            throw new InvalidOperationException('Cannot build column: must define name, label and components');
        }

        return new Column($this->name, $this->label, $this->hidden, $this->components);
    }
}
<?php

namespace Iddigital\Cms\Core\Table\Builder;

use Iddigital\Cms\Core\Exception\InvalidOperationException;
use Iddigital\Cms\Core\Table\Column\Column;
use Iddigital\Cms\Core\Table\IColumn;
use Iddigital\Cms\Core\Table\IColumnComponent;

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
            $this->components = $previous->components;
        }
    }

    /**
     * @return IColumn
     * @throws InvalidOperationException
     */
    protected function build()
    {
        if (!$this->name || !$this->label || !$this->components) {
            throw new InvalidOperationException('Cannot build column: must define name, label and components');
        }

        return new Column($this->name, $this->label, $this->components);
    }
}
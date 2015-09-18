<?php

namespace Iddigital\Cms\Core\Table\DataSource\Definition;

use Iddigital\Cms\Core\Form\Field\Builder\FieldBuilderBase;
use Iddigital\Cms\Core\Form\IField;
use Iddigital\Cms\Core\Table\Builder\Column;
use Iddigital\Cms\Core\Table\IColumn;
use Iddigital\Cms\Core\Table\IColumnComponent;

/**
 * The property column mapping definer class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ColumnMappingDefiner
{
    /**
     * @var callable
     */
    private $columnCallback;

    /**
     * @var callable
     */
    private $componentIdCallback;

    public function __construct(callable $columnCallback, callable $componentIdCallback)
    {
        $this->columnCallback      = $columnCallback;
        $this->componentIdCallback = $componentIdCallback;
    }

    /**
     * Maps to a column component instance.
     *
     * @param IField|FieldBuilderBase|IColumn|IColumnComponent|Column $fieldOrColumnOrComponent
     *
     * @return void
     */
    public function to($fieldOrColumnOrComponent)
    {
        call_user_func(
                $this->columnCallback,
                $fieldOrColumnOrComponent instanceof IColumn
                        ? $fieldOrColumnOrComponent
                        : Column::from($fieldOrColumnOrComponent)
        );
    }

    /**
     * Maps to the component id.
     *
     * @param string $componentId
     *
     * @return void
     */
    public function toComponent($componentId)
    {
        call_user_func($this->componentIdCallback, $componentId);
    }
}
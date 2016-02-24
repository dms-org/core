<?php declare(strict_types = 1);

namespace Dms\Core\Table\DataSource\Definition;

use Dms\Core\Exception\InvalidOperationException;
use Dms\Core\Form\Field\Builder\FieldBuilderBase;
use Dms\Core\Form\IField;
use Dms\Core\Table\Builder\Column;
use Dms\Core\Table\IColumn;
use Dms\Core\Table\IColumnComponent;

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

    /**
     * @var bool
     */
    protected $hidden = false;

    public function __construct(callable $columnCallback, callable $componentIdCallback)
    {
        $this->columnCallback      = $columnCallback;
        $this->componentIdCallback = $componentIdCallback;
    }

    /**
     * Defines the column as hidden
     *
     * @return static
     */
    public function hidden()
    {
        $this->hidden = true;

        return $this;
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
                        : Column::from($fieldOrColumnOrComponent, $this->hidden)
        );
    }

    /**
     * Maps to the component id.
     *
     * @param string $componentId
     *
     * @return void
     * @throws InvalidOperationException
     */
    public function toComponent(string $componentId)
    {
        if ($this->hidden) {
            throw InvalidOperationException::format('Invalid call to %s: cannot hide an existing column', __METHOD__);
        }

        call_user_func($this->componentIdCallback, $componentId);
    }
}
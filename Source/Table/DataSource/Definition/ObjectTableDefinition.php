<?php

namespace Iddigital\Cms\Core\Table\DataSource\Definition;

use Iddigital\Cms\Core\Model\Object\FinalizedClassDefinition;
use Iddigital\Cms\Core\Table\IColumn;
use Iddigital\Cms\Core\Table\TableStructure;

/**
 * The object table definition class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ObjectTableDefinition
{
    /**
     * @var FinalizedClassDefinition
     */
    protected $class;

    /**
     * @var IColumn[]
     */
    protected $columns;

    /**
     * @var string[]
     */
    protected $propertyComponentIdMap = [];

    /**
     * @var callable[]
     */
    protected $componentIdCallableMap = [];

    /**
     * @var callable[]
     */
    protected $customCallableMappers = [];

    /**
     * ObjectTableDefinition constructor.
     *
     * @param FinalizedClassDefinition $class
     */
    public function __construct(FinalizedClassDefinition $class)
    {
        $this->class = $class;
    }

    /**
     * Defines a property to map to a table column component.
     *
     * @param string $propertyName
     *
     * @return ColumnMappingDefiner
     * @throws \Iddigital\Cms\Core\Exception\InvalidArgumentException
     */
    public function property($propertyName)
    {
        $this->class->getProperty($propertyName);

        return new ColumnMappingDefiner(
                function (IColumn $column) use ($propertyName) {
                    $this->column($column);
                    $this->propertyComponentIdMap[$propertyName] = $column->getComponentId();
                },
                function ($componentId) use ($propertyName) {
                    $this->propertyComponentIdMap[$propertyName] = $componentId;
                }
        );
    }

    /**
     * Adds a column to the table.
     *
     * @param IColumn $column
     *
     * @return void
     */
    public function column(IColumn $column)
    {
        $this->columns[$column->getName()] = $column;
    }

    /**
     * Defines a callback to get the data for the mapped column component.
     *
     * @param callable $dataCallback
     *
     * @return ColumnMappingDefiner
     */
    public function computed(callable $dataCallback)
    {
        return new ColumnMappingDefiner(
                function (IColumn $column) use ($dataCallback) {
                    $this->column($column);
                    $this->componentIdCallableMap[$column->getComponentId()] = $dataCallback;
                },
                function ($componentId) use ($dataCallback) {
                    $this->componentIdCallableMap[$componentId] = $dataCallback;
                }
        );
    }

    /**
     * Defines a custom mapping callback.
     *
     * Example:
     * <code>
     * ->custom(function ($row, SomeObject $object) {
     *      $row['column.component'] = $object->component;
     *      $row['column.other']     = $object->getData();
     * });
     * </code>
     *
     * @param callable $customMappingCallback
     *
     * @return void
     */
    public function custom(callable $customMappingCallback)
    {
        $this->customCallableMappers[] = $customMappingCallback;
    }

    /**
     * @return FinalizedObjectTableDefinition
     */
    public function finalize()
    {
        return new FinalizedObjectTableDefinition(
                $this->class,
                new TableStructure($this->columns),
                $this->propertyComponentIdMap,
                $this->componentIdCallableMap,
                $this->customCallableMappers
        );
    }
}
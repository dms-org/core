<?php

namespace Iddigital\Cms\Core\Table\DataSource\Definition;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Model\Criteria\IMemberExpressionParser;
use Iddigital\Cms\Core\Model\Criteria\MemberExpressionParser;
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
     * @var IMemberExpressionParser
     */
    protected $memberParser;

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
     * @param IMemberExpressionParser  $memberParser
     */
    public function __construct(FinalizedClassDefinition $class, IMemberExpressionParser $memberParser = null)
    {
        $this->class        = $class;
        $this->memberParser = $memberParser ?: new MemberExpressionParser();
    }

    /**
     * Defines a member expression to map to a table column component.
     *
     * @param string $memberExpression
     *
     * @return ColumnMappingDefiner
     * @throws InvalidArgumentException
     */
    public function property($memberExpression)
    {
        $this->memberParser->parse($this->class, $memberExpression);

        return new ColumnMappingDefiner(
                function (IColumn $column) use ($memberExpression) {
                    $this->column($column);
                    $this->propertyComponentIdMap[$memberExpression] = $column->getComponentId();
                },
                function ($componentId) use ($memberExpression) {
                    $this->propertyComponentIdMap[$memberExpression] = $componentId;
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
<?php

namespace Iddigital\Cms\Core\Common\Crud\Definition\Table;

use Iddigital\Cms\Core\Common\Crud\Definition\ReadModuleDefinition;
use Iddigital\Cms\Core\Common\Crud\IReadModule;
use Iddigital\Cms\Core\Exception\InvalidOperationException;
use Iddigital\Cms\Core\Form\Builder\Form;
use Iddigital\Cms\Core\Form\Field\Builder\Field;
use Iddigital\Cms\Core\Model\IObjectSet;
use Iddigital\Cms\Core\Model\Object\ArrayDataObject;
use Iddigital\Cms\Core\Model\Object\FinalizedClassDefinition;
use Iddigital\Cms\Core\Module\Definition\Table\TableViewDefiner;
use Iddigital\Cms\Core\Module\Table\TableDisplay;
use Iddigital\Cms\Core\Table\DataSource\Definition\ColumnMappingDefiner;
use Iddigital\Cms\Core\Table\DataSource\Definition\ObjectTableDefinition;
use Iddigital\Cms\Core\Table\DataSource\ObjectTableDataSource;
use Iddigital\Cms\Core\Table\IColumn;
use Iddigital\Cms\Core\Table\ITableDataSource;

/**
 * The summary table definition class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class SummaryTableDefinition
{
    /**
     * @var ReadModuleDefinition
     */
    protected $moduleDefinition;

    /**
     * @var ObjectTableDefinition
     */
    protected $objectTableDefinition;

    /**
     * @var IObjectSet
     */
    protected $dataSource;

    /**
     * @var bool
     */
    protected $isFinishedStructure = false;

    /**
     * @var ITableDataSource
     */
    protected $tableDataSource;

    /**
     * @var TableViewDefiner[]
     */
    protected $viewDefiners = [];

    /**
     * SummaryTableDefinition constructor.
     *
     * @param ReadModuleDefinition     $moduleDefinition
     * @param FinalizedClassDefinition $class
     * @param IObjectSet               $dataSource
     */
    public function __construct(ReadModuleDefinition $moduleDefinition, FinalizedClassDefinition $class, IObjectSet $dataSource)
    {
        $this->moduleDefinition      = $moduleDefinition;
        $this->objectTableDefinition = new ObjectTableDefinition($class);
        $this->dataSource            = $dataSource;
    }

    /**
     * Gets the object table definition object.
     *
     * This method is named map() because of the fluent
     * API of the object table definition.
     *
     * Example:
     * <code>
     * $table->map()->property('name')->to(Field::name('name')->label('Name')->string());
     * </code>
     *
     * @return ObjectTableDefinition
     * @throws InvalidOperationException
     */
    public function map()
    {
        if ($this->isFinishedStructure) {
            throw InvalidOperationException::format(
                    'Invalid call to %s: cannot define table structure after views have been defined',
                    __METHOD__
            );
        }

        return $this->objectTableDefinition;
    }

    /**
     * Maps the property from the supplied object to a table column/component.
     *
     * @param string $propertyName
     *
     * @return ColumnMappingDefiner
     */
    public function mapProperty($propertyName)
    {
        return $this->map()->property($propertyName);
    }

    /**
     * Maps the return value from the supplied callback to a table column/component.
     *
     * The callback will be passed the instance of the object from the data source.
     *
     * Example:
     * <code>
     * $table->mapCallback(function (Person $peron) {
     *      return $person->getFullName();
     * })->to(Field::name('name')->label('Name')->string());
     * </code>
     *
     * @param callable $callback
     *
     * @return ColumnMappingDefiner
     */
    public function mapCallback(callable $callback)
    {
        return $this->map()->computed($callback);
    }

    /**
     * Appends the supplied column to the table structure.
     *
     * @param IColumn $column
     *
     * @return void
     */
    public function column(IColumn $column)
    {
        $this->map()->column($column);
    }

    /**
     * Defines a table view with the supplied name.
     *
     * NOTE: This method must be called **after** the table
     * structure (columns) and property mappings have been defined.
     *
     * @param string $name
     * @param string $label
     *
     * @return TableViewAndReorderDefiner
     */
    public function view($name, $label)
    {
        $dataSource  = $this->finalizeTableStructure();
        $viewDefiner = new TableViewAndReorderDefiner($dataSource, $name, $label, function (callable $reorderCallback) use ($name) {
            $this->definerReorderAction($name, $reorderCallback);
        });

        $this->viewDefiners[] = $viewDefiner;

        return $viewDefiner;
    }

    private function definerReorderAction($viewName, callable $reorderCallback)
    {
        $this->moduleDefinition
                ->objectAction(IReadModule::SUMMARY_TABLE . '.' . $viewName . '.reorder')
                ->form(Form::create()->section('Reorder', [
                        Field::name('new_index')->label('New Index')->int()->required()->greaterThan(0)
                ]))
                ->handler(function ($object, ArrayDataObject $data) use ($reorderCallback) {
                    $reorderCallback($object, $data['new_index']);
                });
    }

    private function finalizeTableStructure()
    {
        if (!$this->isFinishedStructure) {
            $this->tableDataSource = new ObjectTableDataSource(
                    $this->objectTableDefinition->finalize(),
                    $this->dataSource
            );

            $this->isFinishedStructure = true;
        }

        return $this->tableDataSource;
    }

    /**
     * Finalizes the structure of the summary table.
     *
     * @return TableDisplay
     */
    public function finalize()
    {
        $dataSource = $this->finalizeTableStructure();
        $views      = [];

        foreach ($this->viewDefiners as $definer) {
            $views[] = $definer->finalize();
        }

        return new TableDisplay(IReadModule::SUMMARY_TABLE, $dataSource, $views);
    }
}
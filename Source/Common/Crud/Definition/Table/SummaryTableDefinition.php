<?php

namespace Iddigital\Cms\Core\Common\Crud\Definition\Table;

use Iddigital\Cms\Core\Auth\Permission;
use Iddigital\Cms\Core\Common\Crud\Action\Table\CallbackReorderAction;
use Iddigital\Cms\Core\Common\Crud\Action\Table\IReorderAction;
use Iddigital\Cms\Core\Common\Crud\Definition\ReadModuleDefinition;
use Iddigital\Cms\Core\Common\Crud\ICrudModule;
use Iddigital\Cms\Core\Common\Crud\IReadModule;
use Iddigital\Cms\Core\Common\Crud\Table\ISummaryTable;
use Iddigital\Cms\Core\Common\Crud\Table\SummaryTable;
use Iddigital\Cms\Core\Exception\InvalidOperationException;
use Iddigital\Cms\Core\Model\IEntitySet;
use Iddigital\Cms\Core\Model\IObjectSet;
use Iddigital\Cms\Core\Model\Object\FinalizedClassDefinition;
use Iddigital\Cms\Core\Module\Definition\Table\TableViewDefiner;
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
     * @var IReorderAction[]
     */
    protected $viewNameReorderActionMap = [];

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
        $this->objectTableDefinition = new ObjectTableDefinition($class, $dataSource->criteria()->getMemberExpressionParser());
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
     * Maps the member expression from the object data source to a table column/component.
     *
     * @param string $memberExpression
     *
     * @return ColumnMappingDefiner
     */
    public function mapProperty($memberExpression)
    {
        return $this->map()->property($memberExpression);
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
        $viewDefiner = new TableViewAndReorderDefiner(
                $dataSource,
                $name,
                $label,
                function (callable $reorderCallback, array $permissions = [], $actionName = null) use ($name) {
                    $this->definerReorderAction($name, $reorderCallback, $permissions, $actionName);
                }
        );

        $this->viewDefiners[] = $viewDefiner;

        return $viewDefiner;
    }

    private function definerReorderAction($viewName, callable $reorderCallback, array $permissions = [], $actionName = null)
    {
        if (!($this->dataSource instanceof IEntitySet)) {
            throw InvalidOperationException::format(
                    'Cannot define reorder action on non entity set'
            );
        }

        $reorderActionName = $actionName ?: IReadModule::SUMMARY_TABLE . '.' . $viewName . '.reorder';

        $viewPermission = Permission::named(IReadModule::VIEW_PERMISSION);
        $editPermission = Permission::named(ICrudModule::EDIT_PERMISSION);

        if (!in_array($viewPermission, $permissions)) {
            $permissions[] = $viewPermission;
        }

        if (!in_array($editPermission, $permissions)) {
            $permissions[] = $editPermission;
        }

        $reorderAction = new CallbackReorderAction(
                $this->dataSource,
                $reorderActionName,
                $this->moduleDefinition->getAuthSystem(),
                $permissions,
                $reorderCallback
        );

        $this->viewNameReorderActionMap[$viewName] = $reorderAction;
        $this->moduleDefinition->custom()->action($reorderAction);
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
     * @return ISummaryTable
     */
    public function finalize()
    {
        $dataSource = $this->finalizeTableStructure();
        $views      = [];

        foreach ($this->viewDefiners as $definer) {
            $views[] = $definer->finalize();
        }

        return new SummaryTable(IReadModule::SUMMARY_TABLE, $dataSource, $views, $this->viewNameReorderActionMap);
    }
}
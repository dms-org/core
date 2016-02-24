<?php declare(strict_types = 1);

namespace Dms\Core\Common\Crud\Definition\Table;

use Dms\Core\Auth\Permission;
use Dms\Core\Common\Crud\Action\Table\CallbackReorderAction;
use Dms\Core\Common\Crud\Action\Table\IReorderAction;
use Dms\Core\Common\Crud\Definition\ReadModuleDefinition;
use Dms\Core\Common\Crud\ICrudModule;
use Dms\Core\Common\Crud\IReadModule;
use Dms\Core\Common\Crud\Table\ISummaryTable;
use Dms\Core\Common\Crud\Table\SummaryTable;
use Dms\Core\Exception\InvalidOperationException;
use Dms\Core\Model\IEntitySet;
use Dms\Core\Model\IObjectSet;
use Dms\Core\Model\Object\FinalizedClassDefinition;
use Dms\Core\Module\Definition\Table\TableViewDefiner;
use Dms\Core\Table\DataSource\Definition\ColumnMappingDefiner;
use Dms\Core\Table\DataSource\Definition\ObjectTableDefinition;
use Dms\Core\Table\DataSource\ObjectTableDataSource;
use Dms\Core\Table\IColumn;
use Dms\Core\Table\ITableDataSource;

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
    public function map() : ObjectTableDefinition
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
    public function mapProperty(string $memberExpression) : ColumnMappingDefiner
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
    public function mapCallback(callable $callback) : ColumnMappingDefiner
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
    public function view(string $name, string $label) : TableViewAndReorderDefiner
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

        $viewDefiner->load(IReadModule::SUMMARY_TABLE_ID_COLUMN);

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
    public function finalize() : ISummaryTable
    {
        $dataSource = $this->finalizeTableStructure();
        $views      = [];

        foreach ($this->viewDefiners as $definer) {
            $views[] = $definer->finalize();
        }

        return new SummaryTable(IReadModule::SUMMARY_TABLE, $dataSource, $views, $this->viewNameReorderActionMap);
    }
}
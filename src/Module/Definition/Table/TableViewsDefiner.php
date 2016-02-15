<?php declare(strict_types = 1);

namespace Dms\Core\Module\Definition\Table;

use Dms\Core\Module\Table\TableDisplay;
use Dms\Core\Table\ITableDataSource;

/**
 * The table views definer class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TableViewsDefiner extends TableDefinerBase
{
    /**
     * @var ITableDataSource
     */
    private $dataSource;

    /**
     * ArrayTableDefiner constructor.
     *
     * @param string           $name
     * @param callable         $callback
     * @param ITableDataSource $dataSource
     */
    public function __construct(string $name, callable $callback, ITableDataSource $dataSource)
    {
        parent::__construct($name, $callback);
        $this->dataSource = $dataSource;
    }

    /**
     * Defines the views of the table
     *
     * Example:
     * <code>
     * ->withViews(function (TableViewDefinition $view) {
     *      $view->name('default', 'Default')
     *          ->asDefault()
     *          ->orderByAsc('column.component');
     * });
     * </code>
     *
     * @param callable $viewsDefinitionCallback
     *
     * @return void
     */
    public function withViews(callable $viewsDefinitionCallback)
    {
        $definition = new TableViewDefinition($this->dataSource);
        $viewsDefinitionCallback($definition);
        $views = $definition->finalize();

        call_user_func($this->callback, new TableDisplay($this->name, $this->dataSource, $views));
    }

    /**
     * Defines the table display to have no predefined views.
     *
     * @return void
     */
    public function withoutViews()
    {
        $this->withViews(function () {

        });
    }
}
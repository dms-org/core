<?php declare(strict_types = 1);

namespace Dms\Core\Module\Definition\Widget;

use Dms\Core\Auth\IAuthSystem;
use Dms\Core\Auth\IPermission;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Form\IForm;
use Dms\Core\Module\IAction;
use Dms\Core\Table\Chart\IChartDataSource;
use Dms\Core\Table\ITableDataSource;
use Dms\Core\Util\Debug;
use Dms\Core\Widget\ActionWidget;
use Dms\Core\Widget\FormDataWidget;

/**
 * The widget type definer class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class WidgetTypeDefiner extends WidgetDefinerBase
{
    /**
     * @var string
     */
    private $label;

    /**
     * @param string             $name
     * @param string             $label
     * @param IAuthSystem        $authSystem
     * @param IPermission[]      $requiredPermissions
     * @param ITableDataSource[] $tables
     * @param IChartDataSource[] $charts
     * @param IAction[]          $actions
     * @param callable           $callback
     */
    public function __construct(string $name, string $label, IAuthSystem $authSystem, array $requiredPermissions, array $tables, array $charts, array $actions, callable $callback)
    {
        parent::__construct($name, $authSystem, $requiredPermissions, $tables, $charts, $actions, $callback);
        $this->label = $label;
    }

    /**
     * Defines the table of which to load the widget from.
     *
     * @param string $tableName
     *
     * @return TableWidgetDefiner
     * @throws InvalidArgumentException
     */
    public function withTable(string $tableName) : TableWidgetDefiner
    {
        if (!isset($this->tables[$tableName])) {
            throw InvalidArgumentException::format(
                'Invalid table name supplied to %s: expecting one of (%s), \'%s\' given',
                __METHOD__, Debug::formatValues(array_keys($this->tables)), $tableName
            );
        }

        return new TableWidgetDefiner($this->name, $this->label, $this->authSystem, $this->requiredPermissions, $this->tables[$tableName], $this->callback);
    }

    /**
     * Defines the chart of which to load the widget from.
     *
     * @param string $chartName
     *
     * @return ChartWidgetDefiner
     * @throws InvalidArgumentException
     */
    public function withChart(string $chartName) : ChartWidgetDefiner
    {
        if (!isset($this->charts[$chartName])) {
            throw InvalidArgumentException::format(
                'Invalid chart name supplied to %s: expecting one of (%s), \'%s\' given',
                __METHOD__, Debug::formatValues(array_keys($this->charts)), $chartName
            );
        }

        return new ChartWidgetDefiner($this->name, $this->label, $this->authSystem, $this->requiredPermissions, $this->charts[$chartName], $this->callback);
    }

    /**
     * Defines the widget to contain an action from the module.
     *
     * @param string $actionName
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public function withAction(string $actionName)
    {
        if (!isset($this->actions[$actionName])) {
            throw InvalidArgumentException::format(
                'Invalid action name supplied to %s: expecting one of (%s), \'%s\' given',
                __METHOD__, Debug::formatValues(array_keys($this->actions)), $actionName
            );
        }

        call_user_func($this->callback, new ActionWidget($this->name, $this->label, $this->authSystem, $this->requiredPermissions, $this->actions[$actionName]));
    }

    /**
     * Defines the widget to contain the form data from the supplied form.
     *
     * @param IForm $form
     *
     * @throws InvalidArgumentException
     */
    public function withFormData(IForm $form)
    {
        call_user_func($this->callback, new FormDataWidget($this->name, $this->label, $this->authSystem, $this->requiredPermissions, $form));
    }
}
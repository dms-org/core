<?php declare(strict_types = 1);

namespace Dms\Core\Module\Definition;

use Dms\Core\Auth\IAuthSystem;
use Dms\Core\Auth\IPermission;
use Dms\Core\Auth\Permission;
use Dms\Core\Event\IEventDispatcher;
use Dms\Core\Exception\InvalidOperationException;
use Dms\Core\Module\Definition\Chart\ChartDefiner;
use Dms\Core\Module\Definition\Table\TableDefiner;
use Dms\Core\Module\Definition\Widget\WidgetLabelDefiner;
use Dms\Core\Module\IAction;
use Dms\Core\Module\IChartDisplay;
use Dms\Core\Module\ITableDisplay;
use Dms\Core\Module\ModuleLoadingContext;
use Dms\Core\Widget\IWidget;

/**
 * The module definition class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ModuleDefinition
{
    /**
     * @var IAuthSystem
     */
    protected $authSystem;

    /**
     * @var IEventDispatcher
     */
    protected $eventDispatcher;

    /**
     * @var array
     */
    protected $metadata = [];

    /**
     * @var string
     */
    protected $packageName;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var IPermission[]
     */
    protected $requiredPermissions = [];

    /**
     * @var IAction[]
     */
    protected $actions = [];

    /**
     * @var ITableDisplay[]
     */
    protected $tables = [];

    /**
     * @var IChartDisplay[]
     */
    protected $charts = [];

    /**
     * @var IWidget[]
     */
    protected $widgets = [];

    /**
     * ModuleDefinition constructor.
     *
     * @param IAuthSystem $authSystem
     */
    public function __construct(IAuthSystem $authSystem)
    {
        $this->authSystem = $authSystem;
        /** @var ModuleLoadingContext|null $loadingContext */
        $loadingContext    = $authSystem->getIocContainer()->get(ModuleLoadingContext::class);
        $this->packageName = $loadingContext ? $loadingContext->getPackageName() : null;

        $this->eventDispatcher = $authSystem->getEventDispatcher();
    }

    /**
     * @return IAuthSystem
     */
    public function getAuthSystem() : IAuthSystem
    {
        return $this->authSystem;
    }

    /**
     * Defines the name of the module.
     *
     * @param string $name
     *
     * @return void
     */
    public function name(string $name)
    {
        $this->name = $name;

        $this->eventDispatcher->emit(
            $this->packageName . '.' . $this->name . '.define', $this
        );
    }

    /**
     * Adds the supplied metadata to the module.
     *
     * @param array $metadata
     *
     * @return void
     */
    public function metadata(array $metadata)
    {
        $this->metadata = $metadata + $this->metadata;
    }

    /**
     * Requires the supplied permission to access this module
     *
     * @param IPermission|string $permission
     *
     * @return void
     */
    public function authorize($permission)
    {
        $this->requiredPermissions[] = $permission instanceof IPermission
            ? $permission
            : Permission::named($permission);
    }

    /**
     * Requires the supplied permissions to access this module
     *
     * @param IPermission[]|string[] $permissions
     *
     * @return void
     */
    public function authorizeAll(array $permissions)
    {
        foreach ($permissions as $permission) {
            $this->authorize($permission);
        }
    }

    /**
     * Defines an action with the supplied name.
     *
     * @param string $name
     *
     * @return ActionDefiner
     */
    public function action(string $name) : ActionDefiner
    {
        return new ActionDefiner($this->authSystem, $this->requiredPermissions, $name, function (IAction $action) {
            $this->actions[$action->getName()] = $action;
        });
    }

    /**
     * Defines a table data source with the supplied name.
     *
     * @param string $name
     *
     * @return TableDefiner
     */
    public function table(string $name) : Table\TableDefiner
    {
        return new TableDefiner($name, function (ITableDisplay $table) {
            $this->tables[$table->getName()] = $table;
        }, $this->tables);
    }

    /**
     * Defines a chart data source with the supplied name.
     *
     * @param string $name
     *
     * @return ChartDefiner
     */
    public function chart(string $name) : Chart\ChartDefiner
    {
        return new ChartDefiner($name, $this->tables, function (IChartDisplay $chart) {
            $this->charts[$chart->getName()] = $chart;
        });
    }

    /**
     * Defines a widget with the supplied name.
     *
     * @param string $name
     *
     * @return WidgetLabelDefiner
     */
    public function widget(string $name) : Widget\WidgetLabelDefiner
    {
        return new WidgetLabelDefiner($name, $this->authSystem, $this->requiredPermissions, $this->tables, $this->charts, $this->actions,
            function (IWidget $widget) {
                $this->widgets[$widget->getName()] = $widget;
            });
    }

    /**
     * Gets the fluent custom properties definer.
     *
     * @return CustomPropertiesDefiner
     */
    public function custom() : CustomPropertiesDefiner
    {
        return new CustomPropertiesDefiner($this->actions, $this->tables, $this->charts);
    }

    /**
     * @return FinalizedModuleDefinition
     * @throws InvalidOperationException
     */
    public function finalize() : FinalizedModuleDefinition
    {
        $this->verifyCanBeFinalized();

        $this->eventDispatcher->emit(
            $this->packageName . '.' . $this->name . '.defined', $this
        );

        return new FinalizedModuleDefinition(
            $this->name,
            $this->metadata,
            $this->requiredPermissions,
            $this->actions,
            $this->tables,
            $this->charts,
            $this->widgets
        );
    }

    /**
     * @return void
     * @throws InvalidOperationException
     */
    protected function verifyCanBeFinalized()
    {
        if (!$this->name) {
            throw InvalidOperationException::format('Cannot finalize module definition: name has not been defined');
        }
    }
}
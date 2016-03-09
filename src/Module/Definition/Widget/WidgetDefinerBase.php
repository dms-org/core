<?php declare(strict_types = 1);

namespace Dms\Core\Module\Definition\Widget;

use Dms\Core\Auth\IAuthSystem;
use Dms\Core\Auth\IPermission;
use Dms\Core\Module\IAction;
use Dms\Core\Module\IChartDisplay;
use Dms\Core\Module\ITableDisplay;

/**
 * The widget definer base class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class WidgetDefinerBase
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var IAuthSystem
     */
    protected $authSystem;

    /**
     * @var IPermission[]
     */
    protected $requiredPermissions;

    /**
     * @var ITableDisplay[]|null
     */
    protected $tables;

    /**
     * @var IChartDisplay[]|null
     */
    protected $charts;

    /**
     * @var IAction[]|null
     */
    protected $actions;

    /**
     * @var callable
     */
    protected $callback;

    /**
     * WidgetDefiner constructor.
     *
     * @param string               $name
     * @param IAuthSystem          $authSystem
     * @param IPermission[]        $requiredPermissions
     * @param ITableDisplay[]|null $tables
     * @param IChartDisplay[]|null $charts
     * @param IAction[]|null       $actions
     * @param callable             $callback
     */
    public function __construct(string $name, IAuthSystem $authSystem, array $requiredPermissions, array $tables = null, array $charts = null, array $actions = null, callable $callback)
    {
        $this->name                = $name;
        $this->authSystem          = $authSystem;
        $this->requiredPermissions = $requiredPermissions;
        $this->tables              = $tables;
        $this->charts              = $charts;
        $this->actions             = $actions;
        $this->callback            = $callback;
    }
}
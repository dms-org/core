<?php

namespace Dms\Core\Package;

use Dms\Core\Module\IModule;
use Dms\Core\Widget\IWidget;

/**
 * The dashboard widget class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DashboardWidget implements IDashboardWidget
{
    /**
     * @var IModule
     */
    protected $module;

    /**
     * @var IWidget
     */
    protected $widget;

    /**
     * DashboardWidget constructor.
     *
     * @param IModule $module
     * @param IWidget $widget
     */
    public function __construct(IModule $module, IWidget $widget)
    {
        $this->module = $module;
        $this->widget = $widget;
    }

    /**
     * @return IModule
     */
    public function getModule() : IModule
    {
        return $this->module;
    }

    /**
     * @return IWidget
     */
    public function getWidget() : IWidget
    {
        return $this->widget;
    }
}
<?php declare(strict_types = 1);

namespace Dms\Core\Package;

use Dms\Core\Module\IModule;
use Dms\Core\Widget\IWidget;

/**
 * The interface for a dashboard widget.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface IDashboardWidget
{
    /**
     * Gets the module.
     *
     * @return IModule
     */
    public function getModule() : IModule;

    /**
     * Gets the widget.
     *
     * @return IWidget
     */
    public function getWidget() : IWidget;
}

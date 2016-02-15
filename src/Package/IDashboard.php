<?php declare(strict_types = 1);

namespace Dms\Core\Package;

use Dms\Core\Widget\IWidget;

/**
 * The interface for a dashboard.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface IDashboard
{
    /**
     * Gets the widgets.
     *
     * @return IWidget[]
     */
    public function getWidgets() : array;
}

<?php declare(strict_types = 1);

namespace Dms\Core\Package;

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
     * @return IDashboardWidget[]
     */
    public function getWidgets() : array;

    /**
     * Gets the authorized widgets.
     *
     * @return IDashboardWidget[]
     */
    public function getAuthorizedWidgets() : array;
}

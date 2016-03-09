<?php declare(strict_types = 1);

namespace Dms\Core\Package;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Widget\IWidget;

/**
 * The package dashboard class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Dashboard implements IDashboard
{
    /**
     * @var IDashboardWidget[]
     */
    protected $widgets;

    /**
     * Dashboard constructor.
     *
     * @param IDashboardWidget[] $widgets
     */
    public function __construct(array $widgets)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'widgets', $widgets, IDashboardWidget::class);

        $this->widgets = $widgets;
    }

    /**
     * @inheritdoc
     */
    public function getWidgets() : array
    {
        return $this->widgets;
    }

    /**
     * @inheritdoc
     */
    public function getAuthorizedWidgets() : array
    {
        $authorizedWidgets = [];

        foreach ($this->widgets as $widget) {
            if ($widget->getModule()->isAuthorized() && $widget->getWidget()->isAuthorized()) {
                $authorizedWidgets[] = $widget;
            }
        }

        return $authorizedWidgets;
    }
}
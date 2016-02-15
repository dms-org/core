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
     * @var IWidget[]
     */
    protected $widgets;

    /**
     * Dashboard constructor.
     *
     * @param IWidget[] $widgets
     */
    public function __construct(array $widgets)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'widgets', $widgets, IWidget::class);

        $this->widgets = $widgets;
    }

    /**
     * @inheritdoc
     */
    public function getWidgets() : array
    {
        return $this->widgets;
    }
}
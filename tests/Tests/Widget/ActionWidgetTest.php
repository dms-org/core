<?php

namespace Dms\Core\Tests\Widget;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Module\IAction;
use Dms\Core\Widget\ActionWidget;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ActionWidgetTest extends CmsTestCase
{
    public function testNew()
    {
        $action = $this->getMockForAbstractClass(IAction::class);

        $widget = new ActionWidget('action-widget', 'Action', $action);

        $this->assertSame('action-widget', $widget->getName());
        $this->assertSame('Action', $widget->getLabel());
        $this->assertSame($action, $widget->getAction());
    }
}
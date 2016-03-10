<?php

namespace Dms\Core\Tests\Widget;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Auth\IAuthSystem;
use Dms\Core\Auth\Permission;
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

        $authSystem          = $this->getMockForAbstractClass(IAuthSystem::class);
        $requiredPermissions = ['abc' => Permission::named('abc')];

        $widget              = new ActionWidget('action-widget', 'Action', $authSystem, $requiredPermissions, $action);

        $this->assertSame('action-widget', $widget->getName());
        $this->assertSame('Action', $widget->getLabel());
        $this->assertSame($action, $widget->getAction());

        $authSystem->expects(self::exactly(4))
            ->method('isAuthorized')
            ->with($requiredPermissions)
            ->willReturnOnConsecutiveCalls(false, true, false, true);

        // Short circuiting
        $action->expects(self::exactly(2))
            ->method('isAuthorized')
            ->willReturnOnConsecutiveCalls(true, false);

        $this->assertSame(false, $widget->isAuthorized());
        $this->assertSame(true, $widget->isAuthorized());
        $this->assertSame(false, $widget->isAuthorized());
        $this->assertSame(false, $widget->isAuthorized());
    }
}
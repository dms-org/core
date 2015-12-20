<?php

namespace Dms\Core\Tests\Module\Action;

use Dms\Core\Auth\Permission;
use Dms\Core\Module\Handler\CustomUnparameterizedActionHandler;
use Dms\Core\Tests\Module\Action\Fixtures\TestSelfHandlingUnparameterizedAction;
use Dms\Core\Tests\Module\Fixtures\TestDto;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class SelfHandlingUnparameterizedActionTest extends ActionTest
{
    public function testNewAction()
    {
        $action = new TestSelfHandlingUnparameterizedAction($this->mockAuth());

        $this->assertSame('test-unparameterized-action', $action->getName());
        $this->assertEquals([Permission::named('test-permission')], array_values($action->getRequiredPermissions()));
        $this->assertSame(TestDto::class, $action->getReturnTypeClass());
        $this->assertInstanceOf(CustomUnparameterizedActionHandler::class, $action->getHandler());
    }

    public function testRun()
    {
        $action = new TestSelfHandlingUnparameterizedAction(
                $this->mockAuthWithExpectedVerifyCall([Permission::named('test-permission')])
        );

        /** @var TestDto $result */
        $result = $action->run();

        $this->assertInstanceOf(TestDto::class, $result);
        $this->assertSame('123', $result->data);
    }
}
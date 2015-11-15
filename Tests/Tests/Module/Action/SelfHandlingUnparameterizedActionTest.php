<?php

namespace Iddigital\Cms\Core\Tests\Module\Action;

use Iddigital\Cms\Core\Auth\Permission;
use Iddigital\Cms\Core\Module\Handler\CustomParameterizedActionHandler;
use Iddigital\Cms\Core\Module\Handler\CustomUnparameterizedActionHandler;
use Iddigital\Cms\Core\Tests\Module\Action\Fixtures\TestSelfHandlingUnparameterizedAction;
use Iddigital\Cms\Core\Tests\Module\Fixtures\TestDto;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class SelfHandlingUnparameterizedActionTest extends ActionTest
{
    public function testNewAction()
    {
        $action = new TestSelfHandlingUnparameterizedAction($this->mockAuth());

        $this->assertSame('test-unparameterized-action', $action->getName());
        $this->assertEquals([Permission::named('test-permission')], $action->getRequiredPermissions());
        $this->assertSame(TestDto::class, $action->getReturnDtoType());
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
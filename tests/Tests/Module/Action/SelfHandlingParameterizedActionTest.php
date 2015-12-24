<?php

namespace Dms\Core\Tests\Module\Action;

use Dms\Core\Auth\Permission;
use Dms\Core\Exception\TypeMismatchException;
use Dms\Core\Model\Object\ArrayDataObject;
use Dms\Core\Module\Handler\CustomParameterizedActionHandler;
use Dms\Core\Module\Mapping\ArrayDataObjectFormMapping;
use Dms\Core\Tests\Module\Action\Fixtures\TestSelfHandlingParameterizedAction;
use Dms\Core\Tests\Module\Fixtures\TestDto;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class SelfHandlingParameterizedActionTest extends ActionTest
{
    public function testNewAction()
    {
        $action = new TestSelfHandlingParameterizedAction($this->mockAuth());

        $this->assertSame('test-parameterized-action', $action->getName());
        $this->assertEquals([Permission::named('test-permission')], array_values($action->getRequiredPermissions()));
        $this->assertEquals(Permission::named('test-permission'), $action->getRequiredPermission('test-permission'));
        $this->assertSame(TestDto::class, $action->getReturnTypeClass());
        $this->assertInstanceOf(ArrayDataObjectFormMapping::class, $action->getFormDtoMapping());
        $this->assertSame(ArrayDataObject::class, $action->getParameterTypeClass());
        $this->assertInstanceOf(CustomParameterizedActionHandler::class, $action->getHandler());
    }

    public function testRun()
    {
        $action = new TestSelfHandlingParameterizedAction(
                $this->mockAuthWithExpectedVerifyCall([Permission::named('test-permission')])
        );

        /** @var TestDto $result */
        $result = $action->run(['string' => 'abc']);

        $this->assertInstanceOf(TestDto::class, $result);
        $this->assertSame('ABC', $result->data);
    }

    public function testRunHandlerDirectly()
    {
        $action = new TestSelfHandlingParameterizedAction($this->mockAuth());

        /** @var TestDto $result */
        $result = $action->getHandler()->run(new ArrayDataObject(['string' => 'aaaa']));

        $this->assertInstanceOf(TestDto::class, $result);
        $this->assertSame('AAAA', $result->data);
    }

    public function testInvalidDto()
    {
        $this->setExpectedException(TypeMismatchException::class);

        $action = new TestSelfHandlingParameterizedAction($this->mockAuth());

        $action->getHandler()->run(new TestDto('123'));
    }
}
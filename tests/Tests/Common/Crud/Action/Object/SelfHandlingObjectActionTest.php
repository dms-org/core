<?php

namespace Dms\Core\Tests\Common\Crud\Action\Object;

use Dms\Core\Auth\Permission;
use Dms\Core\Common\Crud\Action\Object\IObjectAction;
use Dms\Core\Common\Crud\Action\Object\IObjectActionHandler;
use Dms\Core\Common\Crud\Action\Object\Mapping\ArrayObjectActionFormMapping;
use Dms\Core\Common\Crud\Action\Object\ObjectActionParameter;
use Dms\Core\Exception\TypeMismatchException;
use Dms\Core\Form\InvalidFormSubmissionException;
use Dms\Core\Model\Object\ArrayDataObject;
use Dms\Core\Tests\Common\Crud\Action\Object\Fixtures\ReturnDto;
use Dms\Core\Tests\Common\Crud\Action\Object\Fixtures\TestSelfHandlingObjectAction;
use Dms\Core\Tests\Common\Crud\Action\Object\Mapping\Fixtures\TestEntity;
use Dms\Core\Tests\Module\Action\ActionTest;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class SelfHandlingObjectActionTest extends ActionTest
{

    public function testNewAction()
    {
        $action = new TestSelfHandlingObjectAction($this->mockAuth());

        $this->assertSame('test-object-action', $action->getName());
        $this->assertEquals([Permission::named('test-permission')], array_values($action->getRequiredPermissions()));
        $this->assertSame(ReturnDto::class, $action->getReturnTypeClass());
        $this->assertInstanceOf(ArrayObjectActionFormMapping::class, $action->getFormDtoMapping());

        /** @var IObjectActionHandler $handler */
        $handler = $action->getHandler();
        $this->assertInstanceOf(IObjectActionHandler::class, $handler);
        $this->assertSame(ObjectActionParameter::class, $handler->getParameterTypeClass());
        $this->assertSame(TestEntity::class, $handler->getObjectType());
        $this->assertSame(ArrayDataObject::class, $handler->getDataDtoType());
    }

    public function testSupportedObjects()
    {
        $action = new TestSelfHandlingObjectAction($this->mockAuth());

        $this->assertSame([], $action->getSupportedObjects([]));
        $this->assertEquals(TestEntity::getTestCollection()->asArray(), $action->getSupportedObjects(TestEntity::getTestCollection()->asArray()));
        $this->assertEquals(true, $action->isSupported(TestEntity::withId(2)));

        foreach (TestEntity::getTestCollection() as $entity) {
            $this->assertSame(true, $action->isSupported($entity));
        }

        $this->assertThrows(function () use ($action) {
            $action->getSupportedObjects([new \stdClass()]);
        }, TypeMismatchException::class);
    }

    public function testRun()
    {
        $action = new TestSelfHandlingObjectAction(
                $this->mockAuthWithExpectedVerifyCall([Permission::named('test-permission')])
        );

        /** @var ReturnDto $result */
        $result = $action->run([IObjectAction::OBJECT_FIELD_NAME => 1, 'string' => 'abc']);

        $this->assertInstanceOf(ReturnDto::class, $result);
        $this->assertSame('1_abc', $result->value);
    }

    public function testRunHandlerDirectly()
    {
        $action = new TestSelfHandlingObjectAction($this->mockAuth());

        /** @var ReturnDto $result */
        $result = $action->getHandler()->run(new ObjectActionParameter(
                TestEntity::withId(4),
                new ArrayDataObject(['string' => 'aaaa'])
        ));

        $this->assertInstanceOf(ReturnDto::class, $result);
        $this->assertSame('4_aaaa', $result->value);
    }

    public function testInvalidParamDto()
    {
        $this->setExpectedException(TypeMismatchException::class);

        $action = new TestSelfHandlingObjectAction($this->mockAuth());

        $action->getHandler()->run(new ReturnDto('123'));
    }

    public function testInvalidEntityId()
    {
        /**
         * @see TestEntity::getTestCollection - valid ids are 1, 2, 3
         */
        $this->setExpectedException(InvalidFormSubmissionException::class);

        $action = new TestSelfHandlingObjectAction($this->mockAuth());
        $action->run([IObjectAction::OBJECT_FIELD_NAME => 4, 'string' => 'abc']);
    }
}
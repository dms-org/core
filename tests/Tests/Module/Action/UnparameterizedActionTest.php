<?php

namespace Dms\Core\Tests\Module\Action;

use Dms\Core\Exception\TypeMismatchException;
use Dms\Core\Module\Action\UnparameterizedAction;
use Dms\Core\Module\Handler\CustomUnparameterizedActionHandler;
use Dms\Core\Tests\Module\Handler\Fixtures\ParamDto;
use Dms\Core\Tests\Module\Mock\MockEventDispatcher;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class UnparameterizedActionTest extends ActionTest
{
    public function testNewAction()
    {
        $action = new UnparameterizedAction(
                'name',
                $this->mockAuth(),
                [],
                $handler = new CustomUnparameterizedActionHandler(function () {

                })
        );

        $this->assertSame('name', $action->getName());
        $this->assertSame([], $action->getRequiredPermissions());
        $this->assertSame(null, $action->getReturnTypeClass());
        $this->assertSame($handler, $action->getHandler());
    }

    public function testRunningActionsChecksForPermissions()
    {
        $permissions = $this->mockPermissions(['a', 'b', 'c']);

        $called = false;
        $action = new UnparameterizedAction(
                'name',
                $this->mockAuthWithExpectedVerifyCall($permissions),
                $permissions,
                new CustomUnparameterizedActionHandler(function () use (&$called) {
                    $called = true;
                })
        );

        $action->run();
        $this->assertTrue($called, 'Must call handler');
    }

    public function testCorrectReturnDtoTypes()
    {
        $action = new UnparameterizedAction(
                'name',
                $this->mockAuth(),
                [],
                new CustomUnparameterizedActionHandler(function () {
                    return ParamDto::from('foo');
                }, ParamDto::class)
        );

        $this->assertTrue($action->hasReturnType());
        $this->assertSame(ParamDto::class, $action->getReturnTypeClass());
    }

    public function testWithoutReturnDtoTypes()
    {
        $action = new UnparameterizedAction(
                'name',
                $this->mockAuth(),
                [],
                new CustomUnparameterizedActionHandler(function () {

                })
        );

        $this->assertFalse($action->hasReturnType());
        $this->assertNull($action->getReturnTypeClass());
    }

    public function testVerifiesNullReturnDtoType()
    {
        $return = null;
        $action = new UnparameterizedAction(
                'name',
                $this->mockAuth(),
                [],
                new CustomUnparameterizedActionHandler(function () use (&$return) {
                    return $return;
                })
        );

        $this->assertSame($return, $action->run());

        $this->assertThrows(function () use (&$return, $action) {
            $return = 'abc';
            $action->run();
        }, TypeMismatchException::class);
    }

    public function testVerifiesReturnDtoType()
    {
        $return = ParamDto::from('foo');
        $action = new UnparameterizedAction(
                'name',
                $this->mockAuth(),
                [],
                new CustomUnparameterizedActionHandler(function () use (&$return) {
                    return $return;
                }, ParamDto::class)
        );

        $this->assertSame($return, $action->run());

        $this->assertThrows(function () use (&$return, $action) {
            $return = null;
            $action->run();
        }, TypeMismatchException::class);
    }

    public function testRunAndRanEventsAreEmitted()
    {
        $events = new MockEventDispatcher();
        $auth = $this->mockAuth();
        $auth->method('getEventDispatcher')->willReturn($events);

        $return = ParamDto::from('foo');
        $action = new UnparameterizedAction(
            'action',
            $auth,
            [],
            new CustomUnparameterizedActionHandler(function () use (&$return) {
                return (object)['returned' => 'value'];
            }, \stdClass::class)
        );

        $action->setPackageAndModuleName('package', 'module');

        $action->run();

        $this->assertEquals([
            ['package.module.action.run'],
            ['package.module.action.ran', (object)['returned' => 'value']],
        ], $events->getEmittedEvents());
    }
}
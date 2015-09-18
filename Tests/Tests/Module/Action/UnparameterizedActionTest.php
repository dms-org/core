<?php

namespace Iddigital\Cms\Core\Tests\Module\Action;

use Iddigital\Cms\Core\Exception\TypeMismatchException;
use Iddigital\Cms\Core\Module\Action\UnparameterizedAction;
use Iddigital\Cms\Core\Module\Handler\CustomUnparameterizedActionHandler;
use Iddigital\Cms\Core\Tests\Module\Handler\Fixtures\ParamDto;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class UnparameterizedActionTest extends ActionTest
{

    public function testRunningActionsChecksForPermissions()
    {
        $permissions = $this->mockPermissions(['a', 'b', 'c']);

        $called = false;
        $action = new UnparameterizedAction(
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
                $this->mockAuth(),
                [],
                new CustomUnparameterizedActionHandler(function () {
                    return ParamDto::from('foo');
                }, ParamDto::class)
        );

        $this->assertTrue($action->hasReturnDtoType());
        $this->assertSame(ParamDto::class, $action->getReturnDtoType());
    }

    public function testWithoutReturnDtoTypes()
    {
        $action = new UnparameterizedAction(
                $this->mockAuth(),
                [],
                new CustomUnparameterizedActionHandler(function () {

                })
        );

        $this->assertFalse($action->hasReturnDtoType());
        $this->assertNull($action->getReturnDtoType());
    }

    public function testVerifiesNullReturnDtoType()
    {
        $return = null;
        $action = new UnparameterizedAction(
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
}
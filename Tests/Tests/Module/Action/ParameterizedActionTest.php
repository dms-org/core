<?php

namespace Iddigital\Cms\Core\Tests\Module\Action;

use Iddigital\Cms\Core\Module\Action\ParameterizedAction;
use Iddigital\Cms\Core\Module\Handler\CustomParameterizedActionHandler;
use Iddigital\Cms\Core\Module\Mapping\FormObjectMapping;
use Iddigital\Cms\Core\Exception\TypeMismatchException;
use Iddigital\Cms\Core\Tests\Form\Object\Fixtures\ArrayOfInts;
use Iddigital\Cms\Core\Tests\Form\Object\Fixtures\CreatePageForm;
use Iddigital\Cms\Core\Tests\Form\Object\Fixtures\SeoForm;
use Iddigital\Cms\Core\Tests\Module\Handler\Fixtures\ParamDto;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ParameterizedActionTest extends ActionTest
{
    public function testDtoTypeMismatch()
    {
        $this->setExpectedException(TypeMismatchException::class);

        new ParameterizedAction(
                $this->mockAuth(),
                [],
                new FormObjectMapping(new CreatePageForm()),
                new CustomParameterizedActionHandler(function (SeoForm $form) { })
        );
    }

    public function testCorrectDtoTypes()
    {
        $action = new ParameterizedAction(
                $this->mockAuth(),
                [],
                new FormObjectMapping(ArrayOfInts::withLength(2)),
                new CustomParameterizedActionHandler(function (ArrayOfInts $form) {

                })
        );

        $this->assertFalse($action->hasReturnDtoType());
        $this->assertSame(null, $action->getReturnDtoType());
    }

    public function testCorrectReturnDtoTypes()
    {
        $action = new ParameterizedAction(
                $this->mockAuth(),
                [],
                new FormObjectMapping(ArrayOfInts::withLength(2)),
                new CustomParameterizedActionHandler(function (ArrayOfInts $form) {
                    return ParamDto::from('foo');
                }, ParamDto::class)
        );

        $this->assertTrue($action->hasReturnDtoType());
        $this->assertSame(ParamDto::class, $action->getReturnDtoType());
    }

    public function testRunningActionsChecksForPermissions()
    {
        $permissions = $this->mockPermissions(['a', 'b', 'c']);

        $called = false;
        $action = new ParameterizedAction(
                $this->mockAuthWithExpectedVerifyCall($permissions),
                $permissions,
                new FormObjectMapping(ArrayOfInts::withLength(2)),
                new CustomParameterizedActionHandler(function (ArrayOfInts $form) use (&$called) {
                    $this->assertSame([10, 20], $form->data);
                    $called = true;
                })
        );

        $action->run(['data' => ['10', '20']]);
        $this->assertTrue($called, 'Must call handler');
    }
}
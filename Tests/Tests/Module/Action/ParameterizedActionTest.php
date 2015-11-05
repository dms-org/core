<?php

namespace Iddigital\Cms\Core\Tests\Module\Action;

use Iddigital\Cms\Core\Model\Object\ArrayDataObject;
use Iddigital\Cms\Core\Module\Action\ParameterizedAction;
use Iddigital\Cms\Core\Module\Handler\CustomParameterizedActionHandler;
use Iddigital\Cms\Core\Module\Mapping\ArrayDataObjectFormMapping;
use Iddigital\Cms\Core\Module\Mapping\FormObjectMapping;
use Iddigital\Cms\Core\Exception\TypeMismatchException;
use Iddigital\Cms\Core\Module\Mapping\StagedFormObjectMapping;
use Iddigital\Cms\Core\Tests\Form\Object\Fixtures\ArrayOfInts;
use Iddigital\Cms\Core\Tests\Form\Object\Fixtures\CreatePageForm;
use Iddigital\Cms\Core\Tests\Form\Object\Fixtures\SeoForm;
use Iddigital\Cms\Core\Tests\Form\Object\Stage\Fixtures\ArrayOfIntsStagedForm;
use Iddigital\Cms\Core\Tests\Module\Handler\Fixtures\ParamDto;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ParameterizedActionTest extends ActionTest
{
    public function testNewAction()
    {
        $action = new ParameterizedAction(
                'name',
                $this->mockAuth(),
                [],
                $mapping = new FormObjectMapping(ArrayOfInts::withLength(2)),
                $handler = new CustomParameterizedActionHandler(function (ArrayOfInts $form) { })
        );

        $this->assertSame('name', $action->getName());
        $this->assertSame([], $action->getRequiredPermissions());
        $this->assertSame(null, $action->getReturnDtoType());
        $this->assertSame($mapping, $action->getFormDtoMapping());
        $this->assertSame($handler, $action->getHandler());
    }

    public function testDtoTypeMismatch()
    {
        $this->setExpectedException(TypeMismatchException::class);

        new ParameterizedAction(
            'name',
                $this->mockAuth(),
                [],
                new FormObjectMapping(new CreatePageForm()),
                new CustomParameterizedActionHandler(function (SeoForm $form) { })
        );
    }

    public function testCorrectDtoTypes()
    {
        $action = new ParameterizedAction(
                'name',
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
                'name',
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
                'name',
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

    public function testSubmitFirstStageReturnsEquivalentActionWithStagedFormObject()
    {
        $called = false;
        $action = new ParameterizedAction(
                'name',
                $this->mockAuth(),
                [],
                new StagedFormObjectMapping(new ArrayOfIntsStagedForm()),
                new CustomParameterizedActionHandler(function (ArrayOfIntsStagedForm $form) use (&$called) {
                    $this->assertSame(3, $form->length);
                    $this->assertSame([10, 20, 30], $form->ints);
                    $called = true;
                })
        );

        $this->assertSame(2, $action->getStagedForm()->getAmountOfStages());

        $actionWithoutFirstStage = $action->submitFirstStage(['length' => '3 ']);

        $this->assertInstanceOf(ParameterizedAction::class, $actionWithoutFirstStage);
        $this->assertSame('name', $actionWithoutFirstStage->getName());
        $this->assertSame(1, $actionWithoutFirstStage->getStagedForm()->getAmountOfStages());

        $actionWithoutFirstStage->run(['ints' => ['10', '20', '30']]);
        $this->assertTrue($called, 'Must call handler');
    }

    public function testSubmitFirstStageReturnsEquivalentAction()
    {
        $called = false;
        $action = new ParameterizedAction(
                'name',
                $this->mockAuth(),
                [],
                new ArrayDataObjectFormMapping(new ArrayOfIntsStagedForm()),
                new CustomParameterizedActionHandler(function (ArrayDataObject $form) use (&$called) {
                    $this->assertSame(3, $form['length']);
                    $this->assertSame([10, 20, 30], $form['ints']);
                    $called = true;
                })
        );

        $this->assertSame(2, $action->getStagedForm()->getAmountOfStages());

        $actionWithoutFirstStage = $action->submitFirstStage(['length' => '3 ']);

        $this->assertInstanceOf(ParameterizedAction::class, $actionWithoutFirstStage);
        $this->assertSame(1, $actionWithoutFirstStage->getStagedForm()->getAmountOfStages());

        $actionWithoutFirstStage->run(['ints' => ['10', '20', '30']]);
        $this->assertTrue($called, 'Must call handler');
    }
}
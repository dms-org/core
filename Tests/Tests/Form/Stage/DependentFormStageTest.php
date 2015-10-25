<?php

namespace Iddigital\Cms\Core\Tests\Form\Stage;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Form\Builder\Form;
use Iddigital\Cms\Core\Form\IForm;
use Iddigital\Cms\Core\Form\Stage\DependentFormStage;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DependentFormStageTest extends CmsTestCase
{
    public function testNewFormStage()
    {
        $passedData = null;
        $form       = Form::create()->build();
        $stage      = new DependentFormStage(function (array $previousData) use ($form, &$passedData) {
            $passedData = $previousData;

            return $form;
        }, [], ['some_required_field']);

        $this->assertTrue($stage->requiresPreviousSubmission());
        $this->assertFalse($stage->areAllFieldsRequired());
        $this->assertSame([], $stage->getDefinedFieldNames());
        $this->assertSame(['some_required_field'], $stage->getRequiredFieldNames());
        $this->assertSame($form, $stage->loadForm(['foo' => 'bar']));
        $this->assertSame(['foo' => 'bar'], $passedData);

        $this->assertThrows(function () use ($stage) {
            // Requires data
            $stage->loadForm();
        }, InvalidArgumentException::class);
    }

    public function testBuildsForm()
    {
        $stage = new DependentFormStage(function (array $previousData) {
            return Form::create();
        }, [], null);

        $this->assertInstanceOf(IForm::class, $stage->loadForm([]));
    }

    public function testEmptyRequiredFields()
    {
        $this->setExpectedException(InvalidArgumentException::class);

        new DependentFormStage(function (array $previousData) {
            return Form::create();
        }, [], []);
    }
}
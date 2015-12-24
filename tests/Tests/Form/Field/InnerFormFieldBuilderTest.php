<?php

namespace Dms\Core\Tests\Form\Field;

use Dms\Core\Form\Builder\Form;
use Dms\Core\Form\Field\Builder\BoolFieldBuilder;
use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Form\Field\Processor\InnerFormProcessor;
use Dms\Core\Form\Field\Processor\Validator\GreaterThanOrEqualValidator;
use Dms\Core\Form\Field\Processor\Validator\TypeValidator;
use Dms\Core\Form\InvalidFormSubmissionException;
use Dms\Core\Form\InvalidInputException;
use Dms\Core\Language\Message;
use Dms\Core\Model\Type\ArrayType;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Model\Type\MixedType;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class InnerFormFieldBuilderTest extends FieldBuilderTestBase
{
    /**
     * @param string $name
     * @param string $label
     *
     * @return BoolFieldBuilder
     */
    protected function field($name = 'name', $label = 'Name')
    {
        return Field::name($name)->label($label)->form($this->innerForm());
    }

    public function testInnerFormValidation()
    {
        $field = $this->field()->build();

        $this->assertEquals([
                new TypeValidator(Type::arrayOf(Type::mixed())->nullable()),
                new InnerFormProcessor($this->innerForm()),
        ], $field->getProcessors());

        $this->assertEquals(Type::arrayOf(Type::mixed())->nullable(), $field->getProcessedType());
    }

    /**
     * @return \Dms\Core\Form\IForm
     * @throws \Dms\Core\Form\ConflictingFieldNameException
     */
    protected function innerForm()
    {
        return Form::create()
                ->section('Section', [
                        Field::create()->name('field')->label('Field')->int()->min(0)->required()
                ])
                ->build();
    }
    public function testProcess()
    {
        $this->assertSame(
                ['field' => 10],
                $this->field()->build()->process(['field' => '10'])
        );
    }

    public function testUnprocess()
    {
        $this->assertSame(
                ['field' => 10],
                $this->field()->build()->unprocess(['field' => 10])
        );
    }

    public function testValidationMessages()
    {
        /** @var InvalidFormSubmissionException $e */
        $e = $this->assertThrows(function () {
            $this->field()->build()->process(['field' => -1]);
        }, InvalidFormSubmissionException::class);

        $this->assertEquals(
                [
                        new Message(GreaterThanOrEqualValidator::MESSAGE, [
                                'value' => 0,
                                'field' => 'Field',
                                'input' => -1,
                        ])
                ],
                $e->getAllMessages()
        );
    }

    public function testValidationMessagesInParentForm()
    {
        /** @var InvalidFormSubmissionException $e */
        $e = $this->assertThrows(function () {
            Form::create()->section('Section',[
                $this->field('name', 'Name')
            ])->build()
                ->process(['name' => ['field' => -1]]);
        }, InvalidFormSubmissionException::class);

        $this->assertEquals(
                [
                        new Message(GreaterThanOrEqualValidator::MESSAGE, [
                                'value' => 0,
                                'field' => 'Name > Field',
                                'input' => -1,
                        ])
                ],
                $e->getAllMessages()
        );
    }
}
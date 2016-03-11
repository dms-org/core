<?php

namespace Dms\Core\Tests\Form\Field;

use Dms\Core\Form\Builder\Form;
use Dms\Core\Form\Field\Builder\BoolFieldBuilder;
use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Form\Field\Processor\InnerFormProcessor;
use Dms\Core\Form\Field\Processor\Validator\GreaterThanOrEqualValidator;
use Dms\Core\Form\Field\Processor\Validator\TypeValidator;
use Dms\Core\Form\Field\Type\InnerFormObjectType;
use Dms\Core\Form\Field\Type\InnerFormType;
use Dms\Core\Form\InvalidFormSubmissionException;
use Dms\Core\Form\Object\FormObjectDefinition;
use Dms\Core\Form\Object\IndependentFormObject;
use Dms\Core\Language\Message;
use Dms\Core\Model\Type\Builder\Type;

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

    /**
     * @return \Dms\Core\Form\IForm
     * @throws \Dms\Core\Form\ConflictingFieldNameException
     */
    protected function innerForm()
    {
        return Form::create()
            ->section('Section', [
                Field::create()->name('field')->label('Field')->int()->min(0)->required(),
            ])
            ->build();
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
                ]),
            ],
            $e->getAllMessages()
        );
    }

    public function testValidationMessagesInParentForm()
    {
        /** @var InvalidFormSubmissionException $e */
        $e = $this->assertThrows(function () {
            Form::create()->section('Section', [
                $this->field('name', 'Name'),
            ])->build()->process(['name' => ['field' => -1]]);
        }, InvalidFormSubmissionException::class);

        $this->assertEquals(
            [
                new Message(GreaterThanOrEqualValidator::MESSAGE, [
                    'value' => 0,
                    'field' => 'Name > Field',
                    'input' => -1,
                ]),
            ],
            $e->getAllMessages()
        );
    }

    public function testGetInnerFormAsArrayForm()
    {
        /** @var InnerFormType $type */
        $type = $this->field('name')
            ->value([
                'field' => 10,
            ])
            ->build()
            ->getType();

        $this->assertInstanceOf(InnerFormType::class, $type);

        $innerFormAsArray = $type->getInnerArrayForm('name');

        $this->assertEquals(
            Form::create()
                ->section('Section', [
                    Field::create()
                        ->name('name[field]')
                        ->label('Field')
                        ->int()
                        ->min(0)
                        ->value(10)
                        ->required(),
                ])
                ->build(),
            $innerFormAsArray
        );
    }


    public function testGetInnerFormObjectAsArrayForm()
    {
        $formObject = new class() extends IndependentFormObject
        {
            public $string;

            protected function defineForm(FormObjectDefinition $form)
            {
                $form->section('Details', [
                    $form->field($this->string)->name('string')->label('String')->string()
                ]);
            }
        };

        /** @var InnerFormObjectType $type */
        $type = Field::name('form')->label('Form')
            ->form($formObject)
            ->value($formObject->submitNew([
                'string' => 'abc'
            ]))
            ->build()
            ->getType();

        $this->assertInstanceOf(InnerFormType::class, $type);

        $innerFormAsArray = $type->getInnerArrayForm('name');

        $this->assertEquals(
            Form::create()
                ->section('Details', [
                    Field::create()
                        ->name('name[string]')
                        ->label('String')
                        ->string()
                        ->value('abc'),
                ])
                ->build(),
            $innerFormAsArray
        );
    }
}
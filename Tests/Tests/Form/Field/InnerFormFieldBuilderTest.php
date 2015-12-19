<?php

namespace Iddigital\Cms\Core\Tests\Form\Field;

use Iddigital\Cms\Core\Form\Builder\Form;
use Iddigital\Cms\Core\Form\Field\Builder\BoolFieldBuilder;
use Iddigital\Cms\Core\Form\Field\Builder\Field;
use Iddigital\Cms\Core\Form\Field\Processor\InnerFormProcessor;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\GreaterThanOrEqualValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\TypeValidator;
use Iddigital\Cms\Core\Form\InvalidFormSubmissionException;
use Iddigital\Cms\Core\Form\InvalidInputException;
use Iddigital\Cms\Core\Language\Message;
use Iddigital\Cms\Core\Model\Type\ArrayType;
use Iddigital\Cms\Core\Model\Type\Builder\Type;
use Iddigital\Cms\Core\Model\Type\MixedType;

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
     * @return \Iddigital\Cms\Core\Form\IForm
     * @throws \Iddigital\Cms\Core\Form\ConflictingFieldNameException
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
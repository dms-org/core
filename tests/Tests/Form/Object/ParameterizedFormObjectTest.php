<?php

namespace Dms\Core\Tests\Form\Object;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Form\Builder\Form;
use Dms\Core\Form\Field\Builder\Field as Field;
use Dms\Core\Form\Field\Processor\Validator\ExactArrayLengthValidator;
use Dms\Core\Form\InvalidFormSubmissionException;
use Dms\Core\Language\Message;
use Dms\Core\Model\Type\Builder\Type as Type;
use Dms\Core\Tests\Form\Object\Fixtures\ArrayOfInts;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ParameterizedFormObjectTest extends CmsTestCase
{
    public function testForm()
    {
        $expectedForm = Form::create()
                ->section('Numbers', [
                        Field::name('data')
                                ->label('Numbers')
                                ->arrayOf(Field::element()->int()->required())
                                ->exactLength(4)
                                ->required(),
                ])
                ->build();

        $this->assertEquals($expectedForm, ArrayOfInts::withLength(4)->getForm());
    }

    public function testClassDefinition()
    {
        $class = ArrayOfInts::withLength(10)->getFormDefinition()->getClass();

        $expectedProperties = [
                'data' => Type::arrayOf(Type::int()),
        ];

        $this->assertEquals($expectedProperties, $class->getPropertyTypeMap());
    }

    public function testSubmittingValidForm()
    {
        /** @var ArrayOfInts $form */
        $form = ArrayOfInts::withLength(5)->submit([
                'data' => ['-1', '1', '-2', '2', 0],
        ]);

        $this->assertInstanceOf(ArrayOfInts::class, $form);
        $this->assertSame([-1, 1, -2, 2, 0], $form->data);
    }

    public function testInvalidFormSubmission()
    {
        /** @var InvalidFormSubmissionException $exception */
        $exception = $this->assertThrows(function () {
            ArrayOfInts::withLength(1)->submit([
                    'data' => [1, 2],
            ]);
        }, InvalidFormSubmissionException::class);

        $form = $exception->getForm();
        $this->assertEquals(ArrayOfInts::withLength(1)->getForm(), $form);

        $this->assertEquals([
                'data' => [
                        new Message(ExactArrayLengthValidator::MESSAGE, [
                                'field'  => 'Numbers',
                                'length' => 1,
                                'input'  => [1, 2]
                        ])
                ],
        ], $exception->getFieldMessageMap());
    }
}
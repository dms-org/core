<?php

namespace Iddigital\Cms\Core\Tests\Form\Field;

use Iddigital\Cms\Core\Form\Field\Builder\Field as Field;
use Iddigital\Cms\Core\Form\Field\Builder\DecimalFieldBuilder;
use Iddigital\Cms\Core\Form\Field\Processor\DefaultValueProcessor;
use Iddigital\Cms\Core\Form\Field\Processor\TypeProcessor;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\DecimalPointsValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\FloatValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\GreaterThanOrEqualValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\LessThanOrEqualValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\LessThanValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\RequiredValidator;
use Iddigital\Cms\Core\Form\Field\Type\FloatType;
use Iddigital\Cms\Core\Form\Field\Type\IntType;
use Iddigital\Cms\Core\Language\Message;
use Iddigital\Cms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DecimalFieldBuilderTest extends FieldBuilderTestBase
{
    /**
     * @param string $name
     * @param string $label
     *
     * @return DecimalFieldBuilder
     */
    protected function field($name = 'name', $label = 'Name')
    {
        return Field::name($name)->label($label)->decimal();
    }

    public function testMax()
    {
        $field = $this->field()->max(20.0)->build();

        $this->assertAttributes([FloatType::ATTR_MAX => 20.0], $field);
        $this->assertSame(12.013, $field->process('12.013'));

        $this->assertFieldThrows($field, 21, [
                new Message(LessThanOrEqualValidator::MESSAGE, [
                        'field' => 'Name',
                        'input' => 21,
                        'value' => 20.0,
                ])
        ]);
    }

    public function testDecimalPoints()
    {
        $field = $this->field()->minDecimalPoints(1)->maxDecimalPoints(3)->build();

        $this->assertAttributes([
                FloatType::ATTR_MIN_DECIMAL_POINTS => 1,
                FloatType::ATTR_MAX_DECIMAL_POINTS => 3,
        ], $field);


        $this->assertProcesses([12.0, 12.1, -12.012, 543.431], $field);
        $this->assertFailsToProcess([12.0001, 12.0432, 43243.3244], $field);

        $this->assertFieldThrows($field, 0.0004, [
                new Message(DecimalPointsValidator::MESSAGE_MAX, [
                        'field'              => 'Name',
                        'input'              => 0.0004,
                        'max_decimal_points' => 3,
                ])
        ]);
    }

    public function testFieldWithProcessors()
    {
        $field = $this->field()
                ->min(10)
                ->lessThan(20)
                ->defaultTo(15)
                ->required()
                ->build();

        $this->assertEquals([
                new FloatValidator(Type::mixed()),
                new TypeProcessor('float'),
                new GreaterThanOrEqualValidator(Type::float()->nullable(), 10),
                new LessThanValidator(Type::float()->nullable(), 20),
                new DefaultValueProcessor(Type::float()->nullable(), 15),
                new RequiredValidator(Type::float()->union(Type::int()))
        ], $field->getProcessors());

        $this->assertSame(10.0, $field->getType()->get(IntType::ATTR_MIN));
        $this->assertSame(19.0, $field->getType()->get(IntType::ATTR_MAX));
        $this->assertSame(17.0, $field->process('17.0'));
        $this->assertFieldThrows($field, 20, [
                new Message(LessThanValidator::MESSAGE, [
                        'field' => 'Name',
                        'input' => 20,
                        'value' => 20.0,
                ])
        ]);

        $this->assertEquals(Type::float()->union(Type::int()), $field->getProcessedType());
    }
}
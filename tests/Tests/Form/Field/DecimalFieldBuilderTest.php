<?php

namespace Dms\Core\Tests\Form\Field;

use Dms\Core\Form\Field\Builder\DecimalFieldBuilder;
use Dms\Core\Form\Field\Builder\Field as Field;
use Dms\Core\Form\Field\Processor\DefaultValueProcessor;
use Dms\Core\Form\Field\Processor\TypeProcessor;
use Dms\Core\Form\Field\Processor\Validator\MaxDecimalPointsValidator;
use Dms\Core\Form\Field\Processor\Validator\FloatValidator;
use Dms\Core\Form\Field\Processor\Validator\GreaterThanOrEqualValidator;
use Dms\Core\Form\Field\Processor\Validator\LessThanOrEqualValidator;
use Dms\Core\Form\Field\Processor\Validator\LessThanValidator;
use Dms\Core\Form\Field\Processor\Validator\RequiredValidator;
use Dms\Core\Form\Field\Type\FloatType;
use Dms\Core\Form\Field\Type\IntType;
use Dms\Core\Form\Field\Type\ScalarType;
use Dms\Core\Language\Message;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Model\Type\IType;

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

        $this->assertAttributes([FloatType::ATTR_MAX => 20.0, ScalarType::ATTR_TYPE => IType::FLOAT], $field);
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
        $field = $this->field()->maxDecimalPoints(3)->build();

        $this->assertAttributes([
                ScalarType::ATTR_TYPE              => IType::FLOAT,
                FloatType::ATTR_MAX_DECIMAL_POINTS => 3,
        ], $field);


        $this->assertProcesses([12.0, 12.1, -12.012, 543.431], $field);
        $this->assertFailsToProcess([12.0001, 12.0432, 43243.3244], $field);

        $this->assertFieldThrows($field, 0.0004, [
                new Message(MaxDecimalPointsValidator::MESSAGE, [
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
                new RequiredValidator(Type::mixed()),
                new FloatValidator(Type::mixed()),
                new TypeProcessor('float'),
                new GreaterThanOrEqualValidator(Type::float(), 10),
                new LessThanValidator(Type::float(), 20),
                new DefaultValueProcessor(Type::float(), 15),
        ], $field->getProcessors());

        $this->assertSame(10.0, $field->getType()->get(IntType::ATTR_MIN));
        $this->assertSame(20.0, $field->getType()->get(IntType::ATTR_LESS_THAN));
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
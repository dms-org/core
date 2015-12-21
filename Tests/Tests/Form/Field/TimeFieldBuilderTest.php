<?php

namespace Dms\Core\Tests\Form\Field;

use Dms\Core\Form\Field\Builder\DateFieldBuilder;
use Dms\Core\Form\Field\Builder\Field as Field;
use Dms\Core\Form\Field\Processor\DateTimeProcessor;
use Dms\Core\Form\Field\Processor\DefaultValueProcessor;
use Dms\Core\Form\Field\Processor\Validator\DateFormatValidator;
use Dms\Core\Form\Field\Processor\Validator\GreaterThanOrEqualValidator;
use Dms\Core\Form\Field\Processor\Validator\LessThanOrEqualValidator;
use Dms\Core\Form\Field\Processor\Validator\LessThanValidator;
use Dms\Core\Form\Field\Processor\Validator\TypeValidator;
use Dms\Core\Form\Field\Type\TimeOfDayType;
use Dms\Core\Language\Message;
use Dms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TimeFieldBuilderTest extends FieldBuilderTestBase
{
    /**
     * @param string $name
     * @param string $label
     *
     * @return DateFieldBuilder
     */
    protected function field($name = 'name', $label = 'Name')
    {
        return Field::name($name)->label($label)->time('H:i:s');
    }

    public function testMax()
    {
        $max   = new \DateTime('12:00:00');
        $field = $this->field()->max($max)->build();

        $this->assertAttributes([
                TimeOfDayType::ATTR_FORMAT   => 'H:i:s',
                TimeOfDayType::ATTR_MAX      => new \DateTime('0000-01-01 12:00:00'),
                TimeOfDayType::ATTR_TIMEZONE => null,
        ], $field);

        $this->assertEquals($this->buildTime('06:12:34'), $field->process('06:12:34'));
        $this->assertFieldThrows($field, '16:12:34', [
                new Message(LessThanOrEqualValidator::MESSAGE, [
                        'field' => 'Name',
                        'input' => '16:12:34',
                        'value' => new \DateTime('0000-01-01 12:00:00'),
                ])
        ]);
    }

    public function testFieldWithProcessors()
    {
        $field = $this->field()
                ->min($this->buildTime('01:30:00'))
                ->lessThan($this->buildTime('15:45:00'))
                ->defaultTo($this->buildTime('12:00:00'))
                ->build();

        $this->assertEquals([
                new TypeValidator(Type::string()->nullable()),
                new DateFormatValidator(Type::string()->nullable(), 'H:i:s'),
                new DateTimeProcessor('H:i:s', null, DateTimeProcessor::MODE_ZERO_DATE),
                new GreaterThanOrEqualValidator(Type::object(\DateTimeImmutable::class)->nullable(), $this->buildTime('01:30:00')),
                new LessThanValidator(Type::object(\DateTimeImmutable::class)->nullable(), $this->buildTime('15:45:00')),
                new DefaultValueProcessor(Type::object(\DateTimeImmutable::class)->nullable(), $this->buildTime('12:00:00')),
        ], $field->getProcessors());

        $this->assertEquals($this->buildTime('01:30:00'), $field->getType()->get(TimeOfDayType::ATTR_MIN));
        $this->assertEquals($this->buildTime('15:45:00'), $field->getType()->get(TimeOfDayType::ATTR_LESS_THAN));
        $this->assertEquals($this->buildTime('14:12:12'), $field->process('14:12:12'));
        $this->assertFieldThrows($field, '12:00 PM', [
                new Message(DateFormatValidator::MESSAGE, [
                        'field'  => 'Name',
                        'input'  => '12:00 PM',
                        'format' => 'H:i:s',
                ])
        ]);

        $this->assertEquals(Type::object(\DateTimeImmutable::class), $field->getProcessedType());
    }

    protected function buildTime($time)
    {
        $time = \DateTimeImmutable::createFromFormat('H:i:s', $time);
        $time = $time->setDate(0, 1, 1);

        return $time;
    }
}
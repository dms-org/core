<?php

namespace Iddigital\Cms\Core\Tests\Form\Field;

use Iddigital\Cms\Core\Form\Field\Builder\Field as Field;
use Iddigital\Cms\Core\Form\Field\Builder\DateFieldBuilder;
use Iddigital\Cms\Core\Form\Field\Processor\DateTimeProcessor;
use Iddigital\Cms\Core\Form\Field\Processor\DefaultValueProcessor;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\DateFormatValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\GreaterThanOrEqualValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\LessThanOrEqualValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\LessThanValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\TypeValidator;
use Iddigital\Cms\Core\Form\Field\Type\DateTimeType;
use Iddigital\Cms\Core\Language\Message;
use Iddigital\Cms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DateTimeFieldBuilderTest extends FieldBuilderTestBase
{
    /**
     * @param string $name
     * @param string $label
     *
     * @return DateFieldBuilder
     */
    protected function field($name = 'name', $label = 'Name')
    {
        return Field::name($name)->label($label)->datetime('Y-m-d H:i:s');
    }

    public function testMax()
    {
        $max   = new \DateTime('2000-01-01 13:01:01');
        $field = $this->field()->max($max)->build();

        $this->assertAttributes([
                DateTimeType::ATTR_FORMAT   => 'Y-m-d H:i:s',
                DateTimeType::ATTR_MAX      => $max,
                DateTimeType::ATTR_TIMEZONE => null,
        ], $field);

        $this->assertEquals(new \DateTime('1998-03-21 12:45:45'), $field->process('1998-03-21 12:45:45'));
        $this->assertFieldThrows($field, '2001-03-15 12:07:45', [
                new Message(LessThanOrEqualValidator::MESSAGE, [
                        'field' => 'Name',
                        'input' => '2001-03-15 12:07:45',
                        'value' => $max,
                ])
        ]);
    }

    public function testFieldWithProcessors()
    {
        $field = $this->field()
                ->min(new \DateTime('1970-01-01 00:00:01'))
                ->lessThan(new \DateTime('2000-01-01 00:00:00'))
                ->defaultTo(new \DateTime('1970-01-01 00:00:00'))
                ->build();

        $this->assertEquals([
                new TypeValidator(Type::string()->nullable()),
                new DateFormatValidator(Type::string()->nullable(), 'Y-m-d H:i:s'),
                new DateTimeProcessor('Y-m-d H:i:s'),
                new GreaterThanOrEqualValidator(Type::object(\DateTime::class)->nullable(), new \DateTime('1970-01-01 00:00:01')),
                new LessThanValidator(Type::object(\DateTime::class)->nullable(), new \DateTime('2000-01-01 00:00:00')),
                new DefaultValueProcessor(Type::object(\DateTime::class)->nullable(), new \DateTime('1970-01-01  00:00:00')),
        ], $field->getProcessors());

        $this->assertEquals(new \DateTime('1970-01-01 00:00:01'), $field->getType()->get(DateTimeType::ATTR_MIN));
        $this->assertEquals(new \DateTime('1999-12-31 23:59:59'), $field->getType()->get(DateTimeType::ATTR_MAX));
        $this->assertEquals(new \DateTime('1998-05-03 00:00:00'), $field->process('1998-05-03 00:00:00'));
        $this->assertFieldThrows($field, '06/12/2000 00:00::01', [
                new Message(DateFormatValidator::MESSAGE, [
                        'field'  => 'Name',
                        'input'  => '06/12/2000 00:00::01',
                        'format' => 'Y-m-d H:i:s',
                ])
        ]);

        $this->assertEquals(Type::object(\DateTime::class), $field->getProcessedType());
    }
}
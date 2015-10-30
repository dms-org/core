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
use Iddigital\Cms\Core\Form\Field\Type\DateType;
use Iddigital\Cms\Core\Language\Message;
use Iddigital\Cms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DateFieldBuilderTest extends FieldBuilderTestBase
{
    /**
     * @param string $name
     * @param string $label
     *
     * @return DateFieldBuilder
     */
    protected function field($name = 'name', $label = 'Name')
    {
        return Field::name($name)->label($label)->date('Y-m-d');
    }

    public function testMax()
    {
        $max   = new \DateTimeImmutable('2000-01-01');
        $field = $this->field()->max($max)->build();

        $this->assertAttributes([
                DateType::ATTR_FORMAT   => 'Y-m-d',
                DateType::ATTR_MAX      => $max,
                DateType::ATTR_TIMEZONE => null,
        ], $field);

        $this->assertEquals(new \DateTimeImmutable('1998-03-21'), $field->process('1998-03-21'));
        $this->assertFieldThrows($field, '2001-03-15', [
                new Message(LessThanOrEqualValidator::MESSAGE, [
                        'field' => 'Name',
                        'input' => '2001-03-15',
                        'value' => $max,
                ])
        ]);

    }

    public function testFieldWithProcessors()
    {
        $field = $this->field()
                ->min(new \DateTime('1970-01-01'))
                ->lessThan(new \DateTime('2000-01-01'))
                ->defaultTo(new \DateTime('1970-01-01'))
                ->build();

        $this->assertEquals([
                new TypeValidator(Type::string()->nullable()),
                new DateFormatValidator(Type::string()->nullable(), 'Y-m-d'),
                new DateTimeProcessor('Y-m-d', null, DateTimeProcessor::MODE_ZERO_TIME),
                new GreaterThanOrEqualValidator(Type::object(\DateTimeImmutable::class)->nullable(), new \DateTime('1970-01-01')),
                new LessThanValidator(Type::object(\DateTimeImmutable::class)->nullable(), new \DateTime('2000-01-01')),
                new DefaultValueProcessor(Type::object(\DateTimeImmutable::class)->nullable(), new \DateTimeImmutable('1970-01-01')),
        ], $field->getProcessors());

        $this->assertEquals(new \DateTime('1970-01-01'), $field->getType()->get(DateType::ATTR_MIN));
        $this->assertEquals(new \DateTime('1999-12-31'), $field->getType()->get(DateType::ATTR_MAX));
        $this->assertEquals(new \DateTime('1998-05-03 00:00:00'), $field->process('1998-05-03'));
        $this->assertFieldThrows($field, '06/12/2000', [
                new Message(DateFormatValidator::MESSAGE, [
                        'field'  => 'Name',
                        'input'  => '06/12/2000',
                        'format' => 'Y-m-d',
                ])
        ]);

        $this->assertEquals(Type::object(\DateTimeImmutable::class), $field->getProcessedType());
    }
}
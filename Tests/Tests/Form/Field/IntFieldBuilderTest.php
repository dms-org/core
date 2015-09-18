<?php

namespace Iddigital\Cms\Core\Tests\Form\Field;

use Iddigital\Cms\Core\Form\Field\Builder\Field as Field;
use Iddigital\Cms\Core\Form\Field\Builder\IntFieldBuilder;
use Iddigital\Cms\Core\Form\Field\Processor\DefaultValueProcessor;
use Iddigital\Cms\Core\Form\Field\Processor\TypeProcessor;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\GreaterThanOrEqualValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\IntValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\LessThanOrEqualValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\LessThanValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\RequiredValidator;
use Iddigital\Cms\Core\Form\Field\Type\IntType;
use Iddigital\Cms\Core\Language\Message;
use Iddigital\Cms\Core\Model\Type\Builder\Type;
use Iddigital\Cms\Core\Model\Type\ScalarType;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class IntFieldBuilderTest extends FieldBuilderTestBase
{
    /**
     * @param string $name
     * @param string $label
     *
     * @return IntFieldBuilder
     */
    protected function field($name = 'name', $label = 'Name')
    {
        return Field::name($name)->label($label)->int();
    }

    public function testMax()
    {
        $field = $this->field()->max(20)->build();

        $this->assertAttributes([IntType::ATTR_MAX => 20], $field);
        $this->assertSame(12, $field->process('12'));
        $this->assertFieldThrows($field, 21, [
                new Message(LessThanOrEqualValidator::MESSAGE, [
                        'field' => 'Name',
                        'input' => 21,
                        'value' => 20,
                ])
        ]);
    }

    public function testIntFieldWithProcessors()
    {
        $field = $this->field()
                ->min(10)
                ->lessThan(20)
                ->defaultTo(15)
                ->required()
                ->build();

        $this->assertEquals([
                new IntValidator(Type::mixed()),
                new TypeProcessor('int'),
                new GreaterThanOrEqualValidator(Type::int()->nullable(), 10),
                new LessThanValidator(Type::int()->nullable(), 20),
                new DefaultValueProcessor(Type::int()->nullable(), 15),
                new RequiredValidator(Type::int())
        ], $field->getProcessors());

        $this->assertSame(10, $field->getType()->get(IntType::ATTR_MIN));
        $this->assertSame(19, $field->getType()->get(IntType::ATTR_MAX));
        $this->assertSame(17, $field->process('17'));
        $this->assertFieldThrows($field, 20, [
                new Message(LessThanValidator::MESSAGE, [
                        'field' => 'Name',
                        'input' => 20,
                        'value' => 20,
                ])
        ]);

        $this->assertEquals(Type::int(), $field->getProcessedType());
    }
}
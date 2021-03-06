<?php

namespace Dms\Core\Tests\Form\Field;

use Dms\Core\Form\Field\Builder\Field as Field;
use Dms\Core\Form\Field\Builder\IntFieldBuilder;
use Dms\Core\Form\Field\Processor\DefaultValueProcessor;
use Dms\Core\Form\Field\Processor\EmptyStringToNullProcessor;
use Dms\Core\Form\Field\Processor\TypeProcessor;
use Dms\Core\Form\Field\Processor\Validator\GreaterThanOrEqualValidator;
use Dms\Core\Form\Field\Processor\Validator\IntValidator;
use Dms\Core\Form\Field\Processor\Validator\LessThanOrEqualValidator;
use Dms\Core\Form\Field\Processor\Validator\LessThanValidator;
use Dms\Core\Form\Field\Processor\Validator\RequiredValidator;
use Dms\Core\Form\Field\Processor\Validator\UniquePropertyValidator;
use Dms\Core\Form\Field\Type\IntType;
use Dms\Core\Language\Message;
use Dms\Core\Model\EntityCollection;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Model\Type\IType;
use Dms\Core\Tests\Form\Field\Processor\Validator\Fixtures\TestEntity;

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

        $this->assertAttributes([IntType::ATTR_TYPE => IType::INT, IntType::ATTR_MAX => 20], $field);
        $this->assertSame(12, $field->process('12'));
        $this->assertFieldThrows($field, 21, [
            new Message(LessThanOrEqualValidator::MESSAGE, [
                'field' => 'Name',
                'input' => 21,
                'value' => 20,
            ]),
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
            new RequiredValidator(Type::mixed()),
            new IntValidator(Type::mixed()),
            new TypeProcessor('int'),
            new GreaterThanOrEqualValidator(Type::int(), 10),
            new LessThanValidator(Type::int(), 20),
            new DefaultValueProcessor(Type::int(), 15),
        ], $field->getProcessors());

        $this->assertSame(10, $field->getType()->get(IntType::ATTR_MIN));
        $this->assertSame(20, $field->getType()->get(IntType::ATTR_LESS_THAN));
        $this->assertSame(17, $field->process('17'));
        $this->assertFieldThrows($field, 20, [
            new Message(LessThanValidator::MESSAGE, [
                'field' => 'Name',
                'input' => 20,
                'value' => 20,
            ]),
        ]);

        $this->assertEquals(Type::int(), $field->getProcessedType());
    }

    public function testFieldUniqueValidation()
    {
        $entities = new EntityCollection(TestEntity::class, [
            new TestEntity(1),
            new TestEntity(2),
            new TestEntity(123),
        ]);

        $field = $this->field()
            ->uniqueIn($entities, 'id')
            ->value(123)
            ->build();

        $this->assertEquals([
            new EmptyStringToNullProcessor(Type::mixed()),
            new IntValidator(Type::mixed()),
            new TypeProcessor('int'),
            new UniquePropertyValidator(Type::int()->nullable(), $entities, 'id', 123),
        ], $field->getProcessors());

        $this->assertSame(5, $field->process('5'));
        $this->assertSame(123, $field->process('123'));

        $this->assertFieldThrows($field, '2', [
            new Message(UniquePropertyValidator::MESSAGE, [
                'field'         => 'Name',
                'input'         => '2',
                'property_name' => 'id',
            ]),
        ]);

        $newField = $field->withInitialValue(100);

        $this->assertEquals([
            new EmptyStringToNullProcessor(Type::mixed()),
            new IntValidator(Type::mixed()),
            new TypeProcessor('int'),
            new UniquePropertyValidator(Type::int()->nullable(), $entities, 'id', 100),
        ], $newField->getProcessors());
    }
}
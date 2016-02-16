<?php

namespace Dms\Core\Tests\Form\Field;

use Dms\Core\Form\Field\Builder\ArrayOfFieldBuilder;
use Dms\Core\Form\Field\Builder\Field as Field;
use Dms\Core\Form\Field\Processor\ArrayAllProcessor;
use Dms\Core\Form\Field\Processor\TypeProcessor;
use Dms\Core\Form\Field\Processor\Validator\AllUniquePropertyValidator;
use Dms\Core\Form\Field\Processor\Validator\ArrayUniqueValidator;
use Dms\Core\Form\Field\Processor\Validator\ExactArrayLengthValidator;
use Dms\Core\Form\Field\Processor\Validator\IntValidator;
use Dms\Core\Form\Field\Processor\Validator\MaxArrayLengthValidator;
use Dms\Core\Form\Field\Processor\Validator\MinArrayLengthValidator;
use Dms\Core\Form\Field\Processor\Validator\TypeValidator;
use Dms\Core\Form\Field\Type\ArrayOfType;
use Dms\Core\Language\Message;
use Dms\Core\Model\EntityCollection;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Model\TypedCollection;
use Dms\Core\Tests\Model\Fixtures\TestEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ArrayOfFieldBuilderTest extends FieldBuilderTestBase
{
    /**
     * @param string $name
     * @param string $label
     *
     * @return ArrayOfFieldBuilder
     */
    protected function field($name = 'name', $label = 'Name')
    {
        return Field::name($name)->label($label)->arrayOf(Field::element()->string());
    }

    public function testExactLength()
    {
        $field = $this->field()
            ->exactLength(3)
            ->build();

        $this->assertEquals([
            new TypeValidator(Type::arrayOf(Type::mixed())->nullable()),
            new ExactArrayLengthValidator(Type::arrayOf(Type::mixed())->nullable(), 3),
            new ArrayAllProcessor([new TypeProcessor('string')]),
        ], $field->getProcessors());

        $this->assertSame(3, $field->getType()->get(ArrayOfType::ATTR_EXACT_ELEMENTS));

        $this->assertSame(['1', '2', '3'], $field->process([1, 2, 3]));

        $this->assertFieldThrows($field, [], [
            new Message(ExactArrayLengthValidator::MESSAGE, [
                'field'  => 'Name',
                'input'  => [],
                'length' => 3,
            ]),
        ]);

        $this->assertEquals(Type::arrayOf(Type::string()->nullable())->nullable(), $field->getProcessedType());
    }

    public function testArrayContainsNoDuplications()
    {
        $field = $this->field()
            ->containsNoDuplicates()
            ->build();

        $this->assertEquals([
            new TypeValidator(Type::arrayOf(Type::mixed())->nullable()),
            new ArrayAllProcessor([new TypeProcessor('string')]),
            new ArrayUniqueValidator(Type::arrayOf(Type::string()->nullable())->nullable(), 3),
        ], $field->getProcessors());

        $this->assertSame(true, $field->getType()->get(ArrayOfType::ATTR_UNIQUE_ELEMENTS));

        $this->assertSame(['1', '2', '3'], $field->process([1, 2, 3]));

        $this->assertFieldThrows($field, [1, 1.0], [
            new Message(ArrayUniqueValidator::MESSAGE, [
                'field' => 'Name',
                'input' => ['1', '1'],
            ]),
        ]);
    }

    public function testFieldWithProcessors()
    {
        $field = $this->field()
            ->minLength(2)
            ->maxLength(5)
            ->build();

        $this->assertEquals([
            new TypeValidator(Type::arrayOf(Type::mixed())->nullable()),
            new MinArrayLengthValidator(Type::arrayOf(Type::mixed())->nullable(), 2),
            new MaxArrayLengthValidator(Type::arrayOf(Type::mixed())->nullable(), 5),
            new ArrayAllProcessor([new TypeProcessor('string')]),
        ], $field->getProcessors());

        $this->assertSame(2, $field->getType()->get(ArrayOfType::ATTR_MIN_ELEMENTS));
        $this->assertSame(5, $field->getType()->get(ArrayOfType::ATTR_MAX_ELEMENTS));

        $this->assertSame(['1', '2'], $field->process([1, 2]));

        $this->assertFieldThrows($field, [1], [
            new Message(MinArrayLengthValidator::MESSAGE, [
                'field'  => 'Name',
                'input'  => [1],
                'length' => 2,
            ]),
        ]);

        $this->assertFieldThrows($field, [1, 2, 4, 5, 6, 5, 4], [
            new Message(MaxArrayLengthValidator::MESSAGE, [
                'field'  => 'Name',
                'input'  => [1, 2, 4, 5, 6, 5, 4],
                'length' => 5,
            ]),
        ]);
    }

    public function testAllUniqueIn()
    {
        $entities = new EntityCollection(TestEntity::class, [
            new TestEntity(1),
            new TestEntity(2),
            new TestEntity(3),
        ]);

        $field = Field::name('number')
            ->label('Number')
            ->arrayOf(Field::element()->int())
            ->allUniqueIn($entities, 'id')
            ->value([2, 3])
            ->build();

        $this->assertEquals([
            new TypeValidator(Type::arrayOf(Type::mixed())->nullable()),
            new ArrayAllProcessor([new IntValidator(Type::mixed()), new TypeProcessor('int')]),
            new AllUniquePropertyValidator(Type::arrayOf(Type::int()->nullable())->nullable(), $entities, 'id', [2, 3]),
        ], $field->getProcessors());

        $this->assertSame([5, 9], $field->process(['5', '9']));
        $this->assertSame([2, 3], $field->process(['2', '3']));

        $this->assertFieldThrows($field, [1], [
            new Message(AllUniquePropertyValidator::MESSAGE, [
                'field'         => 'Number',
                'input'         => [1],
                'property_name' => 'id',
            ]),
        ]);

        $newField = $field->withInitialValue([100]);

        $this->assertEquals([
            new TypeValidator(Type::arrayOf(Type::mixed())->nullable()),
            new ArrayAllProcessor([new IntValidator(Type::mixed()), new TypeProcessor('int')]),
            new AllUniquePropertyValidator(Type::arrayOf(Type::int()->nullable())->nullable(), $entities, 'id', [100]),
        ], $newField->getProcessors());
    }

    public function testArrayOfDates()
    {
        $field = Field::forType()->arrayOf(Field::element()->date('Y-m-d'))->build();

        $this->assertEquals(
            [new \DateTimeImmutable('2000-01-01'), new \DateTimeImmutable('2001-01-01')],
            $field->process(['2000-01-01', '2001-01-01'])
        );

        $this->assertEquals(
            ['2000-01-01', '2001-01-01'],
            $field->unprocess([new \DateTimeImmutable('2000-01-01'), new \DateTimeImmutable('2001-01-01')])
        );
    }

    public function testMapToTypedCollection()
    {
        $field = Field::forType()
            ->arrayOf(Field::element()->string()->required())
            ->mapToCollection(Type::collectionOf(Type::string()))
            ->build();

        $this->assertEquals(
            new TypedCollection(Type::string(), ['a', 'b', 'c']),
            $field->process(['a', 'b', 'c'])
        );

        $this->assertEquals(
            ['a', 'b', 'c'],
            $field->unprocess(new TypedCollection(Type::string(), ['a', 'b', 'c']))
        );

        $this->assertEquals(
            Type::collectionOf(Type::string())->nullable(),
            $field->getProcessedType()
        );
    }
}
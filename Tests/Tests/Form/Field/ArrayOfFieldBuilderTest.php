<?php

namespace Iddigital\Cms\Core\Tests\Form\Field;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Form\Field\Builder\ArrayOfFieldBuilder;
use Iddigital\Cms\Core\Form\Field\Builder\Field as Field;
use Iddigital\Cms\Core\Form\Field\Processor\ArrayAllProcessor;
use Iddigital\Cms\Core\Form\Field\Processor\TypeProcessor;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\ExactArrayLengthValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\MaxArrayLengthValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\MinArrayLengthValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\TypeValidator;
use Iddigital\Cms\Core\Form\Field\Type\ArrayOfType;
use Iddigital\Cms\Core\Language\Message;
use Iddigital\Cms\Core\Model\Type\Builder\Type;

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
                new ArrayAllProcessor([new TypeProcessor('string')]),
                new ExactArrayLengthValidator(Type::arrayOf(Type::string()->nullable())->nullable(), 3),
        ], $field->getProcessors());

        $this->assertSame(3, $field->getType()->get(ArrayOfType::ATTR_MIN_ELEMENTS));
        $this->assertSame(3, $field->getType()->get(ArrayOfType::ATTR_MAX_ELEMENTS));

        $this->assertSame(['1', '2', '3'], $field->process([1, 2, 3]));

        $this->assertFieldThrows($field, [], [
                new Message(ExactArrayLengthValidator::MESSAGE, [
                        'field'  => 'Name',
                        'input'  => [],
                        'length' => 3,
                ])
        ]);

        $this->assertEquals(Type::arrayOf(Type::string()->nullable())->nullable(), $field->getProcessedType());
    }

    public function testFieldWithProcessors()
    {
        $field = $this->field()
                ->minLength(2)
                ->maxLength(5)
                ->build();

        $this->assertEquals([
                new TypeValidator(Type::arrayOf(Type::mixed())->nullable()),
                new ArrayAllProcessor([new TypeProcessor('string')]),
                new MinArrayLengthValidator(Type::arrayOf(Type::string()->nullable())->nullable(), 2),
                new MaxArrayLengthValidator(Type::arrayOf(Type::string()->nullable())->nullable(), 5),
        ], $field->getProcessors());

        $this->assertSame(2, $field->getType()->get(ArrayOfType::ATTR_MIN_ELEMENTS));
        $this->assertSame(5, $field->getType()->get(ArrayOfType::ATTR_MAX_ELEMENTS));

        $this->assertSame(['1', '2'], $field->process([1, 2]));

        $this->assertFieldThrows($field, [1], [
                new Message(MinArrayLengthValidator::MESSAGE, [
                        'field'  => 'Name',
                        'input'  => [1],
                        'length' => 2,
                ])
        ]);

        $this->assertFieldThrows($field, [1, 2, 4, 5, 6, 5, 4], [
                new Message(MaxArrayLengthValidator::MESSAGE, [
                        'field'  => 'Name',
                        'input'  => [1, 2, 4, 5, 6, 5, 4],
                        'length' => 5,
                ])
        ]);
    }
}
<?php

namespace Dms\Core\Tests\Table\Column\Component\Type;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Model\Criteria\Condition\ConditionOperator;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Table\Column\Component\Type\ColumnComponentOperator;
use Dms\Core\Table\Column\Component\Type\ColumnComponentType;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ColumnComponentTypeTest extends CmsTestCase
{
    public function testNewType()
    {
        $phpType = Type::string();
        $field   = Field::name('foo')->label('Foo')->string()->build();
        $type    = new ColumnComponentType($phpType, [
                $equals = new ColumnComponentOperator('=', $field),
                $notEqual = new ColumnComponentOperator('!=', $field),
        ]);


        $this->assertSame(true, $type->hasOperator('='));
        $this->assertSame(true, $type->hasOperator('!='));
        $this->assertSame($equals, $type->getOperator('='));
        $this->assertSame($notEqual, $type->getOperator('!='));
        $this->assertSame(['=' => $equals, '!=' => $notEqual], $type->getConditionOperators());
        $this->assertSame(false, $type->hasOperator('>'));
        $this->assertSame($phpType, $type->getPhpType());
    }

    public function testInvalidOperators()
    {
        $this->expectException(InvalidArgumentException::class);

        new ColumnComponentType(Type::string(), [
                new \stdClass()
        ]);
    }

    public function testForStringField()
    {
        $field = Field::name('foo')->label('Foo')->string()->build();

        $type = ColumnComponentType::forField($field);

        $this->assertSame($field->getProcessedType(), $type->getPhpType());
        $this->assertEquals([
                new ColumnComponentOperator('=', $field),
                new ColumnComponentOperator('!=', $field),
                new ColumnComponentOperator(
                        ConditionOperator::IN,
                        Field::name($field->getName())->label($field->getLabel())->arrayOfField($field)->build()
                ),
                new ColumnComponentOperator(
                        ConditionOperator::NOT_IN,
                        Field::name($field->getName())->label($field->getLabel())->arrayOfField($field)->build()
                ),
                new ColumnComponentOperator(ConditionOperator::STRING_CONTAINS, $field),
                new ColumnComponentOperator(ConditionOperator::STRING_CONTAINS_CASE_INSENSITIVE, $field),
        ], array_values($type->getConditionOperators()));
    }

    public function testEquals()
    {
        $stringField = Field::name('foo')->label('Foo')->string()->build();
        $intField    = Field::name('bar')->label('Bar')->int()->build();

        $stringType = ColumnComponentType::forField($stringField);
        $intType    = ColumnComponentType::forField($intField);

        $this->assertTrue($stringType->equals($stringType));
        $this->assertTrue($intType->equals($intType));
        $this->assertTrue($stringType->equals(clone $stringType));

        $this->assertFalse($intType->equals($stringType));
        $this->assertFalse($stringType->equals($intType));
    }

    public function testWithFieldAs()
    {
        $type = ColumnComponentType::forField(Field::name('foo')->label('Foo')->string()->build());

        $type = $type->withFieldAs('bar', 'Bar');

        foreach ($type->getConditionOperators() as $operator) {
            $this->assertSame('bar', $operator->getField()->getName());
            $this->assertSame('Bar', $operator->getField()->getLabel());
        }
    }
}
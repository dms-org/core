<?php

namespace Iddigital\Cms\Core\Tests\Table\Column\Component\Type;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Form\Field\Builder\Field;
use Iddigital\Cms\Core\Model\Criteria\Condition\ConditionOperator;
use Iddigital\Cms\Core\Model\Type\Builder\Type;
use Iddigital\Cms\Core\Table\Column\Component\Type\ColumnComponentOperator;
use Iddigital\Cms\Core\Table\Column\Component\Type\ColumnComponentType;

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
        $this->setExpectedException(InvalidArgumentException::class);

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
}
<?php

namespace Dms\Core\Tests\Table\Column\Component\Type;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Model\Criteria\Condition\ConditionOperator;
use Dms\Core\Table\Column\Component\Type\ColumnComponentOperator;
use Dms\Core\Table\Column\Component\Type\StandardConditions;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class StandardConditionsTest extends CmsTestCase
{
    public function testForIntField()
    {
        $field = Field::name('foo')->label('Foo')->int()->build();

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
                new ColumnComponentOperator('>', $field),
                new ColumnComponentOperator('>=', $field),
                new ColumnComponentOperator('<', $field),
                new ColumnComponentOperator('<=', $field),
        ], StandardConditions::forField($field));
    }

    public function testForStringField()
    {
        $field = Field::name('foo')->label('Foo')->string()->build();

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
        ], StandardConditions::forField($field));
    }

    public function testForStringFieldWithCustomOperators()
    {
        $field = Field::name('foo')->label('Foo')->string()->build();

        $this->assertEquals([
                new ColumnComponentOperator('=', $field),
                new ColumnComponentOperator('!=', $field),
        ], StandardConditions::forField($field, ['=', '!=']));
    }
}
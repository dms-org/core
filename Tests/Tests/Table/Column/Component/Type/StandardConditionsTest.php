<?php

namespace Iddigital\Cms\Core\Tests\Table\Column\Component\Type;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Form\Field\Builder\Field;
use Iddigital\Cms\Core\Model\Criteria\Condition\ConditionOperator;
use Iddigital\Cms\Core\Table\Column\Component\Type\ColumnComponentOperator;
use Iddigital\Cms\Core\Table\Column\Component\Type\StandardConditions;

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
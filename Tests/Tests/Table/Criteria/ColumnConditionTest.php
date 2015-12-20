<?php

namespace Dms\Core\Tests\Table\Criteria;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Table\Builder\Column;
use Dms\Core\Table\Builder\Table;
use Dms\Core\Table\Criteria\ColumnCondition;
use Dms\Core\Table\Data\TableRow;
use Dms\Core\Table\IColumnComponent;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ColumnConditionTest extends CmsTestCase
{
    protected function makeStructure()
    {
        return Table::create([
            Column::from(Field::name('column')->label('Column')->string()->build())
        ]);
    }

    public function testEqualityCondition()
    {
        $structure = $this->makeStructure();
        /** @var IColumnComponent $component */
        list($column, $component) = $structure->getColumnAndComponent('column');
        $equals  = $component->getType()->getOperator('=');

        $condition = new ColumnCondition($column, $component, $equals, 'foo');

        $this->assertSame($column, $condition->getColumn());
        $this->assertSame($component, $condition->getColumnComponent());
        $this->assertSame('column.column', $condition->getComponentId());
        $this->assertSame($equals, $condition->getOperator());
        $this->assertSame('foo', $condition->getValue());

        $filterCallable = $condition->makeRowFilterCallable();
        $this->assertTrue($filterCallable(new TableRow(['column' => ['column' => 'foo']])));
        $this->assertFalse($filterCallable(new TableRow(['column' => ['column' => 'bar']])));

        $getter = $condition->makeComponentGetterCallable();
        $this->assertSame('foo', $getter(new TableRow(['column' => ['column' => 'foo']])));
        $this->assertSame('bar', $getter(new TableRow(['column' => ['column' => 'bar']])));
    }
}
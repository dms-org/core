<?php

namespace Dms\Core\Tests\Table\Criteria;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Model\Criteria\OrderingDirection;
use Dms\Core\Table\Builder\Column;
use Dms\Core\Table\Builder\Table;
use Dms\Core\Table\Criteria\ColumnCondition;
use Dms\Core\Table\Criteria\ColumnOrdering;
use Dms\Core\Table\Data\TableRow;
use Dms\Core\Table\IColumnComponent;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ColumnOrderingTest extends CmsTestCase
{
    protected function makeStructure()
    {
        return Table::create([
            Column::from(Field::name('column')->label('Column')->string()->build())
        ]);
    }

    public function testAscOrdering()
    {
        $structure = $this->makeStructure();
        /** @var IColumnComponent $component */
        list($column, $component) = $structure->getColumnAndComponent('column');

        $ordering = new ColumnOrdering($column, $component, OrderingDirection::ASC);

        $this->assertSame($column, $ordering->getColumn());
        $this->assertSame($component, $ordering->getColumnComponent());
        $this->assertSame(OrderingDirection::ASC, $ordering->getDirection());
        $this->assertSame(true, $ordering->isAsc());
        $this->assertSame('column.column', $ordering->getComponentId());

        $getter = $ordering->makeComponentGetterCallable();
        $this->assertSame('foo', $getter(new TableRow(['column' => ['column' => 'foo']])));
        $this->assertSame('bar', $getter(new TableRow(['column' => ['column' => 'bar']])));
    }

    public function testDescOrdering()
    {
        $structure = $this->makeStructure();
        /** @var IColumnComponent $component */
        list($column, $component) = $structure->getColumnAndComponent('column');

        $ordering = new ColumnOrdering($column, $component, OrderingDirection::DESC);

        $this->assertSame(OrderingDirection::DESC, $ordering->getDirection());
        $this->assertSame(false, $ordering->isAsc());
    }

    public function testInvalidDirection()
    {
        $this->expectException(InvalidArgumentException::class);

        $structure = $this->makeStructure();
        list($column, $component) = $structure->getColumnAndComponent('column');
        new ColumnOrdering($column, $component, 'invalid-direction');
    }
}
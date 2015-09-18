<?php

namespace Iddigital\Cms\Core\Tests\Table\Criteria;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Form\Field\Builder\Field;
use Iddigital\Cms\Core\Model\Criteria\OrderingDirection;
use Iddigital\Cms\Core\Table\Builder\Column;
use Iddigital\Cms\Core\Table\Builder\Table;
use Iddigital\Cms\Core\Table\Criteria\ColumnCondition;
use Iddigital\Cms\Core\Table\Criteria\ColumnOrdering;
use Iddigital\Cms\Core\Table\Data\TableRow;
use Iddigital\Cms\Core\Table\IColumnComponent;

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
        $this->setExpectedException(InvalidArgumentException::class);

        $structure = $this->makeStructure();
        list($column, $component) = $structure->getColumnAndComponent('column');
        new ColumnOrdering($column, $component, 'invalid-direction');
    }
}
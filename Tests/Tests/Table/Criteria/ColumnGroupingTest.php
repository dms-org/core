<?php

namespace Iddigital\Cms\Core\Tests\Table\Criteria;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Form\Field\Builder\Field;
use Iddigital\Cms\Core\Table\Builder\Column;
use Iddigital\Cms\Core\Table\Builder\Table;
use Iddigital\Cms\Core\Table\Criteria\ColumnGrouping;
use Iddigital\Cms\Core\Table\Data\TableRow;
use Iddigital\Cms\Core\Table\IColumnComponent;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ColumnGroupingTest extends CmsTestCase
{
    protected function makeStructure()
    {
        return Table::create([
                Column::from(Field::name('column')->label('Column')->string()->build())
        ]);
    }

    public function testGrouping()
    {
        $structure = $this->makeStructure();
        /** @var IColumnComponent $component */
        list($column, $component) = $structure->getColumnAndComponent('column');

        $grouping = new ColumnGrouping($column, $component);

        $this->assertSame($column, $grouping->getColumn());
        $this->assertSame($component, $grouping->getColumnComponent());
        $this->assertSame('column.column', $grouping->getComponentId());

        $getter = $grouping->makeComponentGetterCallable();
        $this->assertSame('foo', $getter(new TableRow(['column' => ['column' => 'foo']])));
        $this->assertSame('bar', $getter(new TableRow(['column' => ['column' => 'bar']])));
    }
}
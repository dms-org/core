<?php

namespace Iddigital\Cms\Core\Tests\Table\Criteria;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Form\Field\Builder\Field;
use Iddigital\Cms\Core\Model\Criteria\OrderingDirection;
use Iddigital\Cms\Core\Table\Builder\Column;
use Iddigital\Cms\Core\Table\Builder\Table;
use Iddigital\Cms\Core\Table\Criteria\ColumnCondition;
use Iddigital\Cms\Core\Table\Criteria\ColumnGrouping;
use Iddigital\Cms\Core\Table\Criteria\ColumnOrdering;
use Iddigital\Cms\Core\Table\Criteria\RowCriteria;
use Iddigital\Cms\Core\Table\IColumnComponent;
use Iddigital\Cms\Core\Table\ITableStructure;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class RowCriteriaTest extends CmsTestCase
{
    /**
     * @var ITableStructure
     */
    protected $structure;

    /**
     * @var RowCriteria
     */
    protected $criteria;

    public function setUp()
    {
        $this->structure = $this->makeStructure();
        $this->criteria  = new RowCriteria($this->structure);
    }

    protected function makeStructure()
    {
        return Table::create([
                Column::name('name')->label('Name')->components([
                        Field::name('first_name')->label('First Name')->string(),
                        Field::name('last_name')->label('Last Name')->string(),
                ]),
                Column::from(Field::name('age')->label('Age')->int()),
        ]);
    }

    public function testNew()
    {
        $this->assertSame($this->structure, $this->criteria->getStructure());
        $this->assertSame([], $this->criteria->getColumnsToLoad());
        $this->assertSame([], $this->criteria->getColumnNamesToLoad());
        $this->assertSame(false, $this->criteria->getWhetherLoadsAllColumns());
        $this->assertSame([], $this->criteria->getConditions());
        $this->assertSame([], $this->criteria->getOrderings());
        $this->assertSame([], $this->criteria->getGroupings());
        $this->assertSame(null, $this->criteria->getAmountOfRows());
        $this->assertSame(0, $this->criteria->getRowsToSkip());
        $this->assertNotSame($this->criteria, $this->criteria->asNewCriteria());
        $this->assertEquals($this->criteria, $this->criteria->asNewCriteria());
    }

    public function testLoadAllWithoutParameter()
    {
        $this->criteria->loadAll();

        $this->assertSame($this->structure->getColumns(), $this->criteria->getColumnsToLoad());
        $this->assertSame(true, $this->criteria->getWhetherLoadsAllColumns());
    }

    public function testLoadAllWithParameter()
    {
        $this->criteria->loadAll(['name']);

        $this->assertSame(['name' => $this->structure->getColumn('name')], $this->criteria->getColumnsToLoad());
        $this->assertSame(['name'], $this->criteria->getColumnNamesToLoad());
        $this->assertSame(false, $this->criteria->getWhetherLoadsAllColumns());
    }

    public function testLoad()
    {
        $this->criteria->load('age');

        $this->assertSame(['age' => $this->structure->getColumn('age')], $this->criteria->getColumnsToLoad());
        $this->assertSame(['age'], $this->criteria->getColumnNamesToLoad());
        $this->assertSame(false, $this->criteria->getWhetherLoadsAllColumns());
    }

    public function testLoadDuplicates()
    {
        $this->criteria->load('age')->load('age');

        $this->assertSame(['age' => $this->structure->getColumn('age')], $this->criteria->getColumnsToLoad());
    }

    public function testLoadChained()
    {
        $this->criteria->load('age')->load('name');

        $this->assertSame(
                ['age' => $this->structure->getColumn('age'), 'name' => $this->structure->getColumn('name')],
                $this->criteria->getColumnsToLoad()
        );
        $this->assertSame(['age', 'name'], $this->criteria->getColumnNamesToLoad());
        $this->assertSame(true, $this->criteria->getWhetherLoadsAllColumns());
    }

    public function testLoadInvalidColumn()
    {
        $this->assertThrows(function () {
            $this->criteria->loadAll(['non_existent']);
        }, InvalidArgumentException::class);

        $this->assertThrows(function () {
            $this->criteria->load('non_existent');
        }, InvalidArgumentException::class);
    }

    public function testWhere()
    {
        $this->criteria->where('name.first_name', '=', 'foo');

        /** @var IColumnComponent $component */
        list($column, $component) = $this->structure->getColumnAndComponent('name.first_name');
        $this->assertEquals([
                new ColumnCondition($column, $component, $component->getType()->getOperator('='), 'foo')
        ], $this->criteria->getConditions());
    }

    public function testOrderBy()
    {
        $this->criteria
                ->orderBy('name.first_name', OrderingDirection::ASC)
                ->orderBy('name.last_name', OrderingDirection::DESC);

        $name = $this->structure->getColumn('name');
        $this->assertEquals([
                new ColumnOrdering($name, $name->getComponent('first_name'), OrderingDirection::ASC),
                new ColumnOrdering($name, $name->getComponent('last_name'), OrderingDirection::DESC),
        ], $this->criteria->getOrderings());
    }

    public function testOrderByAscAndDesc()
    {
        $this->criteria
                ->orderByAsc('name.first_name')
                ->orderByDesc('name.last_name');

        $name = $this->structure->getColumn('name');
        $this->assertEquals([
                new ColumnOrdering($name, $name->getComponent('first_name'), OrderingDirection::ASC),
                new ColumnOrdering($name, $name->getComponent('last_name'), OrderingDirection::DESC),
        ], $this->criteria->getOrderings());
    }

    public function testGroupBy()
    {
        $this->criteria->groupBy('age');

        list($column, $component) = $this->structure->getColumnAndComponent('age');
        $this->assertEquals([
                new ColumnGrouping($column, $component)
        ], $this->criteria->getGroupings());
    }

    public function testGroupByAutoLoadsColumn()
    {
        $this->criteria->groupBy('age');

        $this->assertSame(['age' => $this->structure->getColumn('age')], $this->criteria->getColumnsToLoad());
    }

    public function testOffsetAndLimit()
    {
        $this->criteria->skipRows(10)->maxRows(25);

        $this->assertSame(10, $this->criteria->getRowsToSkip());
        $this->assertSame(25, $this->criteria->getAmountOfRows());
    }

    public function testFromExisting()
    {
        $this->criteria
                ->loadAll()
                ->where('name.last_name', '!=', null)
                ->groupBy('age')
                ->orderByAsc('name.first_name')
                ->skipRows(10)
                ->maxRows(25);

        $this->assertEquals(RowCriteria::fromExisting($this->criteria), $this->criteria);
    }
}
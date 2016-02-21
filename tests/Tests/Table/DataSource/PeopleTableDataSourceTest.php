<?php

namespace Dms\Core\Tests\Table\DataSource;

use Dms\Core\Model\Criteria\Condition\ConditionOperator;
use Dms\Core\Model\Criteria\OrderingDirection;
use Dms\Core\Table\IRowCriteria;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class PeopleTableDataSourceTest extends TableDataSourceTest
{
    public function testNormalizesSingleComponentColumns()
    {
        $this->assertLoadsSections([
                [
                        ['name' => ['first_name' => 'Joe', 'last_name' => 'Go'], 'age' => ['age' => 29]],
                        ['name' => ['first_name' => 'Harold', 'last_name' => 'Php'], 'age' => ['age' => 38]],
                        ['name' => ['first_name' => 'Samantha', 'last_name' => 'Sharp'], 'age' => ['age' => 20]],
                        ['name' => ['first_name' => 'Joe', 'last_name' => 'Java'], 'age' => ['age' => 32]],
                        ['name' => ['first_name' => 'Kelly', 'last_name' => 'Rust'], 'age' => ['age' => 18]],
                ]
        ]);
    }

    public function testGreaterThenCriteria()
    {
        $this->assertLoadsSections([
                [
                        ['name' => ['first_name' => 'Harold', 'last_name' => 'Php'], 'age' => ['age' => 38]],
                ]
        ], $this->dataSource->criteria()->loadAll()->where('age', '>', 35));
    }

    public function testOrderByCriteria()
    {
        $this->assertLoadsSections([
                [
                        ['name' => ['first_name' => 'Harold', 'last_name' => 'Php'], 'age' => ['age' => 38]],
                        ['name' => ['first_name' => 'Joe', 'last_name' => 'Go'], 'age' => ['age' => 29]],
                        ['name' => ['first_name' => 'Joe', 'last_name' => 'Java'], 'age' => ['age' => 32]],
                        ['name' => ['first_name' => 'Kelly', 'last_name' => 'Rust'], 'age' => ['age' => 18]],
                        ['name' => ['first_name' => 'Samantha', 'last_name' => 'Sharp'], 'age' => ['age' => 20]],
                ]
        ], $this->dataSource->criteria()->loadAll()
                ->orderBy('name.first_name', OrderingDirection::ASC)
                ->orderBy('name.last_name', OrderingDirection::ASC)
        );
    }

    public function testCriteriaWithOrConditionMode()
    {
        $this->assertLoadsSections([
                [
                        ['name' => ['first_name' => 'Kelly', 'last_name' => 'Rust'], 'age' => ['age' => 18]],
                        ['name' => ['first_name' => 'Samantha', 'last_name' => 'Sharp'], 'age' => ['age' => 20]],
                        ['name' => ['first_name' => 'Harold', 'last_name' => 'Php'], 'age' => ['age' => 38]],
                ]
        ], $this->dataSource->criteria()
                ->loadAll()
                ->setConditionMode(IRowCriteria::CONDITION_MODE_OR)
                ->where('age', '>', 35)
                ->where('age', '<', 25)
                ->orderBy('age', OrderingDirection::ASC)
        );
    }

    public function testGrouping()
    {
        $this->assertLoadsSections([
                [
                        'group_data' => ['name' => ['first_name' => 'Joe']],
                        ['name' => ['first_name' => 'Joe', 'last_name' => 'Go'], 'age' => ['age' => 29]],
                        ['name' => ['first_name' => 'Joe', 'last_name' => 'Java'], 'age' => ['age' => 32]],
                ],
                [
                        'group_data' => ['name' => ['first_name' => 'Harold']],
                        ['name' => ['first_name' => 'Harold', 'last_name' => 'Php'], 'age' => ['age' => 38]],
                ],
                [
                        'group_data' => ['name' => ['first_name' => 'Samantha']],
                        ['name' => ['first_name' => 'Samantha', 'last_name' => 'Sharp'], 'age' => ['age' => 20]],
                ],
                [
                        'group_data' => ['name' => ['first_name' => 'Kelly']],
                        ['name' => ['first_name' => 'Kelly', 'last_name' => 'Rust'], 'age' => ['age' => 18]],
                ],
        ], $this->dataSource->criteria()->loadAll()
                ->groupBy('name.first_name')
        );
    }

    public function testOffsetAndLimit()
    {
        $this->assertLoadsSections([
                [
                        ['name' => ['first_name' => 'Samantha', 'last_name' => 'Sharp'], 'age' => ['age' => 20]],
                ]
        ], $this->dataSource->criteria()->loadAll()
                ->skipRows(2)
                ->maxRows(1)
        );
    }

    public function testCount()
    {
        $this->assertLoadsCount(5);
    }

    public function testCountWithWhere()
    {
        $this->assertLoadsCount(2, $this->dataSource->criteria()->loadAll()->where('age', '>=', 18)->where('age', '<=', 25));
    }

    public function testComplexCriteria()
    {
        $this->assertLoadsSections([
                [
                        'group_data' => ['name' => ['first_name' => 'Joe']],
                        ['name' => ['first_name' => 'Joe', 'last_name' => 'Go'], 'age' => ['age' => 29]],
                        ['name' => ['first_name' => 'Joe', 'last_name' => 'Java'], 'age' => ['age' => 32]],
                ],
                [
                        'group_data' => ['name' => ['first_name' => 'Harold']],
                        ['name' => ['first_name' => 'Harold', 'last_name' => 'Php'], 'age' => ['age' => 38]],
                ],
        ], $this->dataSource->criteria()->loadAll()
                ->where('name.first_name', ConditionOperator::STRING_CONTAINS_CASE_INSENSITIVE, 'O')
                ->orderBy('name.first_name', OrderingDirection::DESC)
                ->orderBy('age', OrderingDirection::ASC)
                ->groupBy('name.first_name')
        );
    }

    public function testLoadPartial()
    {
        $this->assertLoadsSections([
                [
                        ['age' => ['age' => 29]],
                        ['age' => ['age' => 38]],
                        ['age' => ['age' => 20]],
                        ['age' => ['age' => 32]],
                        ['age' => ['age' => 18]],
                ]
        ], $this->dataSource->criteria()->loadAll(['age']));
    }

    public function testLoadEmpty()
    {
        $this->assertLoadsSections([
                [
                        [],
                        [],
                        [],
                        [],
                        [],
                ]
        ], $this->dataSource->criteria());
    }

    public function testLoadPartialWithComplexCriteria()
    {
        $this->assertLoadsSections([
                [
                        'group_data' => ['name' => ['first_name' => 'Joe']],
                        ['name' => ['first_name' => 'Joe', 'last_name' => 'Go']],
                        ['name' => ['first_name' => 'Joe', 'last_name' => 'Java']],
                ],
                [
                        'group_data' => ['name' => ['first_name' => 'Harold']],
                        ['name' => ['first_name' => 'Harold', 'last_name' => 'Php']],
                ],
        ], $this->dataSource->criteria()
                ->load('name')
                ->where('name.first_name', ConditionOperator::STRING_CONTAINS_CASE_INSENSITIVE, 'O')
                ->orderBy('name.first_name', OrderingDirection::DESC)
                ->orderBy('age', OrderingDirection::ASC)
                ->groupBy('name.first_name'));
    }
}
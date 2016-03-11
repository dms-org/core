<?php

namespace Dms\Core\Tests\Table\DataSource;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Model\Criteria\Condition\ConditionOperator;
use Dms\Core\Model\Criteria\OrderingDirection;
use Dms\Core\Table\Builder\Column;
use Dms\Core\Table\Builder\Table;
use Dms\Core\Table\DataSource\ArrayTableDataSource;
use Dms\Core\Table\DataSource\Definition\GroupedTableDefinition;
use Dms\Core\Table\DataSource\GroupedTableDataSourceAdapter;
use Dms\Core\Table\IRowCriteria;
use Dms\Core\Table\ITableDataSource;
use Dms\Core\Table\ITableStructure;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class GroupedTableDataSourceTest extends TableDataSourceTest
{

    /**
     * @return ITableStructure
     */
    protected function buildStructure()
    {
        return Table::create([
            Column::name('name')->label('Name')->components([
                Field::name('first_name')->label('First Name')->string(),
            ]),
            Column::from(Field::name('age_sum')->label('Age Sum')->int()),
            Column::from(Field::name('average_age')->label('Average Age')->decimal()),
            Column::from(Field::name('amount')->label('Amount')->int()),
        ]);
    }

    /**
     * @param ITableStructure $structure
     *
     * @return ITableDataSource
     */
    protected function buildDataSource(ITableStructure $structure)
    {
        $innerStructure = Table::create([
            Column::name('name')->label('Name')->components([
                Field::name('first_name')->label('First Name')->string(),
                Field::name('last_name')->label('Last Name')->string(),
            ]),
            Column::from(Field::name('age')->label('Age')->int()),
        ]);

        $innerDataSource = new ArrayTableDataSource($innerStructure, [
            ['name' => ['first_name' => 'Joe', 'last_name' => 'Go'], 'age' => 25],
            ['name' => ['first_name' => 'Harold', 'last_name' => 'Php'], 'age' => 38],
            ['name' => ['first_name' => 'Samantha', 'last_name' => 'Sharp'], 'age' => 20],
            ['name' => ['first_name' => 'Joe', 'last_name' => 'Java'], 'age' => 32],
            ['name' => ['first_name' => 'Samantha', 'last_name' => 'Rust'], 'age' => 18],
        ]);

        $definition = new GroupedTableDefinition($innerDataSource);
        $definition->groupedBy('name.first_name');

        $definition->sum('age')->to(Field::name('age_sum')->label('Age Sum')->int());
        $definition->average('age')->to(Field::name('average_age')->label('Average Age')->decimal());
        $definition->count()->to(Field::name('amount')->label('Amount')->int());

        return new GroupedTableDataSourceAdapter($definition->finalize());
    }

    public function testLoadAll()
    {
        $this->assertLoadsSections(DataTableHelper::normalizeSingleComponents([
            [
                ['name' => ['first_name' => 'Joe'], 'age_sum' => 25 + 32, 'average_age' => (25 + 32) / 2, 'amount' => 2],
                ['name' => ['first_name' => 'Harold'], 'age_sum' => 38, 'average_age' => 38.0, 'amount' => 1],
                ['name' => ['first_name' => 'Samantha'], 'age_sum' => 20 + 18, 'average_age' => (20 + 18) / 2, 'amount' => 2],
            ]
        ]));
    }

    public function testMappingFilterCriteriaOfGroupedColumn()
    {
        $this->assertLoadsSections(DataTableHelper::normalizeSingleComponents([
            [
                ['name' => ['first_name' => 'Joe'], 'age_sum' => 25 + 32, 'average_age' => (25 + 32) / 2, 'amount' => 2],
            ]
        ]), $this->dataSource->criteria()
            ->loadAll()
            ->where('name.first_name', '=', 'Joe')
        );

        $this->assertLoadsSections(DataTableHelper::normalizeSingleComponents([
            [
                ['name' => ['first_name' => 'Harold'], 'age_sum' => 38, 'average_age' => 38.0, 'amount' => 1],
                ['name' => ['first_name' => 'Samantha'], 'age_sum' => 20 + 18, 'average_age' => (20 + 18) / 2, 'amount' => 2],
            ]
        ]), $this->dataSource->criteria()
            ->loadAll()
            ->where('name.first_name', '!=', 'Joe')
        );

        $this->assertLoadsSections(DataTableHelper::normalizeSingleComponents([
            [
                ['name' => ['first_name' => 'Samantha'], 'age_sum' => 20 + 18, 'average_age' => (20 + 18) / 2, 'amount' => 2],
            ]
        ]), $this->dataSource->criteria()
            ->loadAll()
            ->where('name.first_name', '!=', 'Harold')
            ->where('name.first_name', '!=', 'Joe')
        );
    }

    public function testMappingGroupedComponentsViaOrderBy()
    {
        $this->assertLoadsSections(DataTableHelper::normalizeSingleComponents([
            [
                ['name' => ['first_name' => 'Harold'], 'age_sum' => 38, 'average_age' => 38.0, 'amount' => 1],
                ['name' => ['first_name' => 'Joe'], 'age_sum' => 25 + 32, 'average_age' => (25 + 32) / 2, 'amount' => 2],
                ['name' => ['first_name' => 'Samantha'], 'age_sum' => 20 + 18, 'average_age' => (20 + 18) / 2, 'amount' => 2],
            ]
        ]), $this->dataSource->criteria()
            ->loadAll()
            ->orderByAsc('name.first_name')
        );

        $this->assertLoadsSections(DataTableHelper::normalizeSingleComponents([
            [
                ['name' => ['first_name' => 'Samantha'], 'age_sum' => 20 + 18, 'average_age' => (20 + 18) / 2, 'amount' => 2],
                ['name' => ['first_name' => 'Joe'], 'age_sum' => 25 + 32, 'average_age' => (25 + 32) / 2, 'amount' => 2],
                ['name' => ['first_name' => 'Harold'], 'age_sum' => 38, 'average_age' => 38.0, 'amount' => 1],
            ]
        ]), $this->dataSource->criteria()
            ->loadAll()
            ->orderByDesc('name.first_name')
        );
    }

    public function testGrouping()
    {
        $this->assertLoadsSections(DataTableHelper::normalizeSingleComponents([
            [
                'group_data' => ['amount' => 2],
                ['name' => ['first_name' => 'Joe'], 'age_sum' => 25 + 32, 'average_age' => (25 + 32) / 2, 'amount' => 2],
                ['name' => ['first_name' => 'Samantha'], 'age_sum' => 20 + 18, 'average_age' => (20 + 18) / 2, 'amount' => 2],
            ],
            [
                'group_data' => ['amount' => 1],
                ['name' => ['first_name' => 'Harold'], 'age_sum' => 38, 'average_age' => 38.0, 'amount' => 1],
            ],
        ]), $this->dataSource->criteria()
            ->loadAll()
            ->groupBy('amount')
        );
    }
}
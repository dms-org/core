<?php

namespace Dms\Core\Tests\Table\Chart\DataSource;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Model\Criteria\OrderingDirection;
use Dms\Core\Table\Builder\Column;
use Dms\Core\Table\Builder\Table;
use Dms\Core\Table\Chart\DataSource\Definition\ChartTableMapperDefinition;
use Dms\Core\Table\Chart\Structure\ChartAxis;
use Dms\Core\Table\Chart\Structure\LineChart;
use Dms\Core\Table\DataSource\ArrayTableDataSource;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ChartTableDataSourceAdapterTest extends CmsTestCase
{
    protected function makeTableDataSource()
    {
        return new ArrayTableDataSource(
                Table::create([
                        Column::name('name')->label('Name')->components([
                                Field::name('first_name')->label('First Name')->string(),
                                Field::name('last_name')->label('Last Name')->string(),
                        ]),
                        Column::from(Field::name('age')->label('Age')->int()),
                        Column::from(Field::name('gender')->label('Gender')->string()->oneOf(['M', 'F'])),
                        Column::from(Field::name('salary')->label('Salary')->int()),
                ]),
                [
                        ['name' => ['first_name' => 'Joe', 'last_name' => 'Go'], 'age' => 29, 'gender' => 'M', 'salary' => 60000],
                        ['name' => ['first_name' => 'Harold', 'last_name' => 'Php'], 'age' => 38, 'gender' => 'M', 'salary' => 72000],
                        ['name' => ['first_name' => 'Samantha', 'last_name' => 'Sharp'], 'age' => 20, 'gender' => 'F', 'salary' => 70000],
                        ['name' => ['first_name' => 'Joe', 'last_name' => 'Java'], 'age' => 32, 'gender' => 'M', 'salary' => 80000],
                        ['name' => ['first_name' => 'Kelly', 'last_name' => 'Rust'], 'age' => 18, 'gender' => 'F', 'salary' => 65000],
                ]
        );
    }

    protected function createLineChart()
    {
        return $this->makeTableDataSource()->asChart(function (ChartTableMapperDefinition $map) {
            $map->structure(new LineChart(
                    $map->column('age')->toAxis(),
                    $map->column('salary')->toAxis()
            ));
        });
    }

    public function testNew()
    {
        $chart = $this->createLineChart();

        $this->assertEquals(new LineChart(
                ChartAxis::forField(Field::name('age')->label('Age')->int()->build()),
                ChartAxis::forField(Field::name('salary')->label('Salary')->int()->build())
        ), $chart->getStructure());

        $criteria = $chart->criteria();

        $this->assertSame($chart->getStructure(), $criteria->getStructure());
    }

    public function testLoadingAllData()
    {
        $chart = $this->createLineChart();

        $chartDataTable = $chart->load();
        $this->assertSame($chart->getStructure(), $chartDataTable->getStructure());

        $this->assertEquals([
                ['age' => ['age' => 29], 'salary' => ['salary' => 60000]],
                ['age' => ['age' => 38], 'salary' => ['salary' => 72000]],
                ['age' => ['age' => 20], 'salary' => ['salary' => 70000]],
                ['age' => ['age' => 32], 'salary' => ['salary' => 80000]],
                ['age' => ['age' => 18], 'salary' => ['salary' => 65000]],
        ], $chartDataTable->getRows());
    }

    public function testLoadWithCriteriaWithOrdering()
    {
        $chart = $this->createLineChart();

        $this->assertEquals([
                ['age' => ['age' => 18], 'salary' => ['salary' => 65000]],
                ['age' => ['age' => 20], 'salary' => ['salary' => 70000]],
                ['age' => ['age' => 29], 'salary' => ['salary' => 60000]],
                ['age' => ['age' => 32], 'salary' => ['salary' => 80000]],
                ['age' => ['age' => 38], 'salary' => ['salary' => 72000]],
        ], $chart->load(
                $chart->criteria()->orderBy('age', OrderingDirection::ASC)
        )->getRows());
    }

    public function testLoadCriteriaWithCondition()
    {
        $chart = $this->createLineChart();

        $this->assertEquals([
                ['age' => ['age' => 38], 'salary' => ['salary' => 72000]],
                ['age' => ['age' => 20], 'salary' => ['salary' => 70000]],
                ['age' => ['age' => 32], 'salary' => ['salary' => 80000]],
        ], $chart->load(
                $chart->criteria()->where('salary', '>=', 70000)
        )->getRows());
    }

    public function testComplexCriteria()
    {
        $chart = $this->createLineChart();

        $this->assertEquals([
                ['age' => ['age' => 38], 'salary' => ['salary' => 72000]],
                ['age' => ['age' => 18], 'salary' => ['salary' => 65000]],
        ], $chart->load(
                $chart->criteria()
                        ->where('salary', '>=', 65000)
                        ->where('salary', '<', 80000)
                        ->orderBy('age', OrderingDirection::DESC)
                        ->where('age', '!=', 20)
        )->getRows());
    }

    protected function createLineChartWithComputedColumn()
    {
        return $this->makeTableDataSource()->asChart(function (ChartTableMapperDefinition $map) {
            $map->structure(new LineChart(
                    $map->computed(function ($row) {
                        $lowerAgeBracket = floor($row['age'] / 10) * 10;

                        return $lowerAgeBracket . '-' . ($lowerAgeBracket + 10);
                    })->requiresColumn('age')->toAxis('age_group', 'Age Group', Field::element()->string()),
                    $map->column('salary')->toAxis()
            ));
        });
    }

    public function testLoadAllComputedColumn()
    {
        $chart = $this->createLineChartWithComputedColumn();

        $this->assertEquals([
                ['salary' => ['salary' => 60000], 'age_group' => ['age_group' => '20-30']],
                ['salary' => ['salary' => 72000], 'age_group' => ['age_group' => '30-40']],
                ['salary' => ['salary' => 70000], 'age_group' => ['age_group' => '20-30']],
                ['salary' => ['salary' => 80000], 'age_group' => ['age_group' => '30-40']],
                ['salary' => ['salary' => 65000], 'age_group' => ['age_group' => '10-20']],
        ], $chart->load()->getRows());
    }
}
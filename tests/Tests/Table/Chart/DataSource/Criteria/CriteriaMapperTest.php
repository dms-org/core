<?php

namespace Dms\Core\Tests\Table\Chart\DataSource\Criteria;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Model\Criteria\OrderingDirection;
use Dms\Core\Table\Builder\Column;
use Dms\Core\Table\Builder\Table;
use Dms\Core\Table\Chart\Criteria\ChartCriteria;
use Dms\Core\Table\Chart\DataSource\Criteria\ChartTableCriteriaMapper;
use Dms\Core\Table\Chart\DataSource\Criteria\CriteriaMappingException;
use Dms\Core\Table\Chart\DataSource\Definition\ChartTableMapperDefinition;
use Dms\Core\Table\Chart\IChartStructure;
use Dms\Core\Table\Chart\Structure\LineChart;
use Dms\Core\Table\DataSource\ArrayTableDataSource;
use Dms\Core\Table\ITableDataSource;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CriteriaMapperTest extends CmsTestCase
{
    /**
     * @var IChartStructure
     */
    protected $structure;

    /**
     * @var ChartTableCriteriaMapper
     */
    protected $mapper;

    /**
     * @var ITableDataSource
     */
    protected $tableDataSource;

    protected function setUp(): void
    {
        $tableDataSource = new ArrayTableDataSource(
                Table::create([
                        Column::from(Field::name('age')->label('Age')->int()),
                        Column::from(Field::name('salary')->label('Salary')->int()),
                ]),
                []
        );

        $map = new ChartTableMapperDefinition($tableDataSource);

        $map->structure(new LineChart(
                $map->column('age')->toAxis(),
                $map->column('salary')->toAxis()
        ));

        $definition            = $map->finalize();
        $this->mapper          = new ChartTableCriteriaMapper($definition);
        $this->structure       = $definition->getStructure();
        $this->tableDataSource = $definition->getTableDataSource();
    }

    public function testEmptyCriteria()
    {
        $criteria = new ChartCriteria($this->structure);

        $this->assertEquals($this->tableDataSource->criteria()->loadAll(), $this->mapper->mapCriteria($criteria));
    }

    public function testOrdering()
    {
        $criteria = new ChartCriteria($this->structure);

        $criteria->orderBy('age', OrderingDirection::ASC);

        $this->assertEquals(
                $this->tableDataSource->criteria()
                        ->loadAll()
                        ->orderBy('age', OrderingDirection::ASC),
                $this->mapper->mapCriteria($criteria)
        );
    }

    public function testCondition()
    {
        $criteria = new ChartCriteria($this->structure);

        $criteria->where('salary', '>=', 75000);

        $this->assertEquals(
                $this->tableDataSource->criteria()
                        ->loadAll()
                        ->where('salary', '>=', 75000),
                $this->mapper->mapCriteria($criteria)
        );
    }

    public function testThrowsExceptionWhenComponentMappedToCallable()
    {
        $chart = $this->tableDataSource->asChart(function (ChartTableMapperDefinition $map) {
            $map->structure(new LineChart(
                    $map->column('age')->toAxis(),
                    $map->computed(function ($row) {
                        return $row['salary'] / 1000;
                    })->toAxis('salary', 'Salary', Field::element()->decimal())
            ));
        });

        $mapper          = new ChartTableCriteriaMapper($chart->getDefinition());

        $this->assertThrows(function () use ($chart, $mapper) {
            $mapper->mapCriteria($chart->criteria()->where('salary', '>', 0));
        }, CriteriaMappingException::class);

        $this->assertThrows(function () use ($chart, $mapper) {
            $mapper->mapCriteria($chart->criteria()->orderBy('salary', OrderingDirection::ASC));
        }, CriteriaMappingException::class);

        $mapper->mapCriteria($chart->criteria()->orderBy('age', OrderingDirection::ASC));
    }
}
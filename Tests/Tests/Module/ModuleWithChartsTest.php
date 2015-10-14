<?php

namespace Iddigital\Cms\Core\Tests\Module;

use Iddigital\Cms\Core\Auth\IPermission;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Form\Field\Builder\Field;
use Iddigital\Cms\Core\Module\Module;
use Iddigital\Cms\Core\Table\Chart\DataSource\ChartTableDataSourceAdapter;
use Iddigital\Cms\Core\Table\Chart\Structure\ChartAxis;
use Iddigital\Cms\Core\Table\Column\Component\ColumnComponent;
use Iddigital\Cms\Core\Tests\Module\Fixtures\ModuleWithCharts;
use Iddigital\Cms\Core\Tests\Module\Mock\MockAuthSystem;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ModuleWithChartsTest extends ModuleTestBase
{

    /**
     * @return Module
     */
    protected function buildModule()
    {
        return new ModuleWithCharts(new MockAuthSystem());
    }

    /**
     * @return IPermission[]
     */
    protected function expectedPermissions()
    {
        return [];
    }

    /**
     * @return string
     */
    protected function expectedName()
    {
        return 'test-module-with-charts';
    }

    public function testChartGetters()
    {
        $this->assertSame(true, $this->module->hasChart('line-chart'));
        $this->assertSame(true, $this->module->hasChart('pie-chart'));
        $this->assertSame(false, $this->module->hasChart('foo-chart'));


        $this->assertInstanceOf(ChartTableDataSourceAdapter::class, $this->module->getChart('line-chart'));
        $this->assertInstanceOf(ChartTableDataSourceAdapter::class, $this->module->getChart('pie-chart'));

        $this->assertSame(
                ['line-chart' => ChartTableDataSourceAdapter::class, 'pie-chart' => ChartTableDataSourceAdapter::class],
                array_map('get_class', $this->module->getCharts())
        );

        $this->assertThrows(function () {
            $this->module->getChart('non-existent-chart');
        }, InvalidArgumentException::class);
    }

    public function testLineChart()
    {
        /** @var ChartTableDataSourceAdapter $chart */
        $chart     = $this->module->getChart('line-chart');
        $dataTable = $this->module->getTable('data-table');

        $this->assertSame($dataTable, $chart->getDefinition()->getTableDataSource());
        $this->assertSame('line-chart', $chart->getName());

        $this->assertEquals([
                'x' => new ChartAxis('x', 'X-Val', [
                        $dataTable->getStructure()->getComponent('x'),
                ]),
                'y' => new ChartAxis('y', 'Y-Val', [
                        $dataTable->getStructure()->getComponent('y'),
                        $dataTable->getStructure()->getComponent('y2'),
                ])
        ], $chart->getStructure()->getAxes());

        $this->assertCount(3, $chart->load()->getRows());
    }

    public function testPieChart()
    {
        /** @var ChartTableDataSourceAdapter $chart */
        $chart = $this->module->getChart('pie-chart');
        $dataTable = $this->module->getTable('data-table');

        $this->assertSame($dataTable, $chart->getDefinition()->getTableDataSource());
        $this->assertSame('pie-chart', $chart->getName());

        $this->assertEquals([
                'is_even' => new ChartAxis('is_even', 'Is Even', [
                        ColumnComponent::forField(Field::name('is_even')->label('Is Even')->bool()->build())
                ]),
                'y' => new ChartAxis('y', 'Y-Val', [
                        $dataTable->getStructure()->getComponent('y'),
                ])
        ], $chart->getStructure()->getAxes());

        $this->assertCount(3, $chart->load()->getRows());
    }
}
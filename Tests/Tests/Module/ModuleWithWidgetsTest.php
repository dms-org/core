<?php

namespace Iddigital\Cms\Core\Tests\Module;

use Iddigital\Cms\Core\Auth\IPermission;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Module\Module;
use Iddigital\Cms\Core\Table\Chart\IChartCriteria;
use Iddigital\Cms\Core\Table\IRowCriteria;
use Iddigital\Cms\Core\Table\Widget\DataSource\WidgetTableDataSourceAdapter;
use Iddigital\Cms\Core\Table\Widget\Structure\WidgetAxis;
use Iddigital\Cms\Core\Tests\Module\Fixtures\ModuleWithWidgets;
use Iddigital\Cms\Core\Tests\Module\Mock\MockAuthSystem;
use Iddigital\Cms\Core\Widget\ChartWidget;
use Iddigital\Cms\Core\Widget\TableWidget;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ModuleWithWidgetsTest extends ModuleTestBase
{

    /**
     * @return Module
     */
    protected function buildModule()
    {
        return new ModuleWithWidgets(new MockAuthSystem());
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
        return 'test-module-with-widgets';
    }

    public function testWidgetGetters()
    {
        $this->assertSame(true, $this->module->hasWidget('table-widget.all'));
        $this->assertSame(true, $this->module->hasWidget('chart-widget.all'));
        $this->assertSame(false, $this->module->hasWidget('foo-widget'));


        $this->assertInstanceOf(TableWidget::class, $this->module->getWidget('table-widget.all'));
        $this->assertInstanceOf(ChartWidget::class, $this->module->getWidget('chart-widget.all'));

        $this->assertSame(
                [
                        'table-widget.all'           => TableWidget::class,
                        'table-widget.with-criteria' => TableWidget::class,
                        'chart-widget.all'           => ChartWidget::class,
                        'chart-widget.with-criteria' => ChartWidget::class,
                ],
                array_map('get_class', $this->module->getWidgets())
        );

        $this->assertThrows(function () {
            $this->module->getWidget('non-existent-widget');
        }, InvalidArgumentException::class);
    }

    public function testTableWidgetWithoutCriteria()
    {
        /** @var TableWidget $widget */
        $widget    = $this->module->getWidget('table-widget.all');
        $dataTable = $this->module->getTable('data-table')->getDataSource();

        $this->assertSame($dataTable, $widget->getTableDataSource());
        $this->assertSame('table-widget.all', $widget->getName());
        $this->assertSame('Table Widget #1', $widget->getLabel());
        $this->assertSame(false, $widget->hasCriteria());
        $this->assertSame(null, $widget->getCriteria());
    }

    public function testTableWidgetWithCriteria()
    {
        /** @var TableWidget $widget */
        $widget    = $this->module->getWidget('table-widget.with-criteria');
        $dataTable = $this->module->getTable('data-table')->getDataSource();

        $this->assertSame($dataTable, $widget->getTableDataSource());
        $this->assertSame('table-widget.with-criteria', $widget->getName());
        $this->assertSame('Table Widget #2', $widget->getLabel());
        $this->assertSame(true, $widget->hasCriteria());
        $this->assertInstanceOf(IRowCriteria::class, $widget->getCriteria());
        $this->assertCount(1, $widget->getCriteria()->getConditions());
    }

    public function testChartWidgetWithoutCriteria()
    {
        /** @var ChartWidget $widget */
        $widget    = $this->module->getWidget('chart-widget.all');
        $lineChart = $this->module->getChart('line-chart')->getDataSource();

        $this->assertSame($lineChart, $widget->getChartDataSource());
        $this->assertSame('chart-widget.all', $widget->getName());
        $this->assertSame('Chart Widget #1', $widget->getLabel());
        $this->assertSame(false, $widget->hasCriteria());
        $this->assertSame(null, $widget->getCriteria());
    }

    public function testChartWidgetWithCriteria()
    {
        /** @var ChartWidget $widget */
        $widget    = $this->module->getWidget('chart-widget.with-criteria');
        $lineChart = $this->module->getChart('line-chart')->getDataSource();

        $this->assertSame($lineChart, $widget->getChartDataSource());
        $this->assertSame('chart-widget.with-criteria', $widget->getName());
        $this->assertSame('Chart Widget #2', $widget->getLabel());
        $this->assertSame(true, $widget->hasCriteria());
        $this->assertInstanceOf(IChartCriteria::class, $widget->getCriteria());
        $this->assertCount(2, $widget->getCriteria()->getConditions());
    }
}
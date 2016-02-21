<?php

namespace Dms\Core\Tests\Module;

use Dms\Core\Auth\IPermission;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Module\Module;
use Dms\Core\Table\Chart\IChartCriteria;
use Dms\Core\Table\IRowCriteria;
use Dms\Core\Tests\Module\Fixtures\ModuleWithWidgets;
use Dms\Core\Tests\Module\Mock\MockAuthSystem;
use Dms\Core\Widget\ActionWidget;
use Dms\Core\Widget\ChartWidget;
use Dms\Core\Widget\TableWidget;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ModuleWithWidgetsTest extends ModuleTestBase
{

    /**
     * @param MockAuthSystem $authSystem
     *
     * @return Module
     */
    protected function buildModule(MockAuthSystem $authSystem)
    {
        return new ModuleWithWidgets($authSystem);
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
        $this->assertSame(true, $this->module->hasWidget('action-widget'));
        $this->assertSame(false, $this->module->hasWidget('foo-widget'));

        $this->assertInstanceOf(TableWidget::class, $this->module->getWidget('table-widget.all'));
        $this->assertInstanceOf(ChartWidget::class, $this->module->getWidget('chart-widget.all'));
        $this->assertInstanceOf(ActionWidget::class, $this->module->getWidget('action-widget'));

        $this->assertSame(
                [
                        'table-widget.all'           => TableWidget::class,
                        'table-widget.with-criteria' => TableWidget::class,
                        'chart-widget.all'           => ChartWidget::class,
                        'chart-widget.with-criteria' => ChartWidget::class,
                        'action-widget' => ActionWidget::class,
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
        $dataTable = $this->module->getTable('data-table');

        $this->assertSame($dataTable, $widget->getTableDisplay());
        $this->assertSame('table-widget.all', $widget->getName());
        $this->assertSame('Table Widget #1', $widget->getLabel());
        $this->assertSame(false, $widget->hasCriteria());
        $this->assertSame(null, $widget->getCriteria());
    }

    public function testTableWidgetWithCriteria()
    {
        /** @var TableWidget $widget */
        $widget    = $this->module->getWidget('table-widget.with-criteria');
        $dataTable = $this->module->getTable('data-table');

        $this->assertSame($dataTable, $widget->getTableDisplay());
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
        $lineChart = $this->module->getChart('line-chart');

        $this->assertSame($lineChart, $widget->getChartDisplay());
        $this->assertSame('chart-widget.all', $widget->getName());
        $this->assertSame('Chart Widget #1', $widget->getLabel());
        $this->assertSame(false, $widget->hasCriteria());
        $this->assertSame(null, $widget->getCriteria());
    }

    public function testChartWidgetWithCriteria()
    {
        /** @var ChartWidget $widget */
        $widget    = $this->module->getWidget('chart-widget.with-criteria');
        $lineChart = $this->module->getChart('line-chart');

        $this->assertSame($lineChart, $widget->getChartDisplay());
        $this->assertSame('chart-widget.with-criteria', $widget->getName());
        $this->assertSame('Chart Widget #2', $widget->getLabel());
        $this->assertSame(true, $widget->hasCriteria());
        $this->assertInstanceOf(IChartCriteria::class, $widget->getCriteria());
        $this->assertCount(2, $widget->getCriteria()->getConditions());
    }

    public function testActionWidget()
    {
        /** @var ActionWidget $widget */
        $widget = $this->module->getWidget('action-widget');
        $action = $this->module->getAction('module-action');

        $this->assertSame($action, $widget->getAction());
        $this->assertSame('action-widget', $widget->getName());
        $this->assertSame('Action Widget #1', $widget->getLabel());
    }
}
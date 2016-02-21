<?php

namespace Dms\Core\Tests\Widget;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Module\IChartDisplay;
use Dms\Core\Table\Chart\IChartCriteria;
use Dms\Core\Table\Chart\IChartDataSource;
use Dms\Core\Table\Chart\IChartDataTable;
use Dms\Core\Widget\ChartWidget;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ChartWidgetTest extends CmsTestCase
{
    public function testNewWithCriteria()
    {
        $chart      = $this->getMockForAbstractClass(IChartDisplay::class);
        $dataSource = $this->getMockForAbstractClass(IChartDataSource::class);
        $criteria   = $this->getMockForAbstractClass(IChartCriteria::class);

        $widget = new ChartWidget('chart-widget', 'Chart', $chart, $criteria);

        $this->assertSame('chart-widget', $widget->getName());
        $this->assertSame('Chart', $widget->getLabel());
        $this->assertSame($chart, $widget->getChartDisplay());
        $this->assertSame($criteria, $widget->getCriteria());
        $this->assertSame(true, $widget->hasCriteria());
        $this->assertSame(true, $widget->isAuthorized());

        $chart->expects(self::once())
                ->method('getDataSource')
                ->willReturn($dataSource);

        $dataSource->expects(self::once())
                ->method('load')
                ->with($criteria)
                ->willReturn($mock = $this->getMock(IChartDataTable::class));

        $this->assertSame($mock, $widget->loadData());
    }

    public function testNewWithoutCriteria()
    {
        /** @var IChartDisplay $chart */
        $chart      = $this->getMockForAbstractClass(IChartDisplay::class);
        $dataSource = $this->getMockForAbstractClass(IChartDataSource::class);

        $widget = new ChartWidget('chart-widget', 'Chart', $chart);

        $this->assertSame('chart-widget', $widget->getName());
        $this->assertSame('Chart', $widget->getLabel());
        $this->assertSame($chart, $widget->getChartDisplay());
        $this->assertSame(null, $widget->getCriteria());
        $this->assertSame(false, $widget->hasCriteria());

        $chart->expects(self::once())
                ->method('getDataSource')
                ->willReturn($dataSource);

        $dataSource->expects(self::once())
                ->method('load')
                ->with(null)
                ->willReturn($mock = $this->getMock(IChartDataTable::class));

        $this->assertSame($mock, $widget->loadData());
    }
}
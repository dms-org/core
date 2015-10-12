<?php

namespace Iddigital\Cms\Core\Tests\Widget;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Table\Chart\IChartCriteria;
use Iddigital\Cms\Core\Table\Chart\IChartDataSource;
use Iddigital\Cms\Core\Widget\ChartWidget;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ChartWidgetTest extends CmsTestCase
{
    public function testNewWithCriteria()
    {
        $chart = $this->getMockForAbstractClass(IChartDataSource::class);
        $criteria = $this->getMockForAbstractClass(IChartCriteria::class);

        $widget = new ChartWidget('chart-widget', 'Chart', $chart, $criteria);

        $this->assertSame('chart-widget', $widget->getName());
        $this->assertSame('Chart', $widget->getLabel());
        $this->assertSame($chart, $widget->getChartDataSource());
        $this->assertSame($criteria, $widget->getCriteria());
        $this->assertSame(true, $widget->hasCriteria());

        $chart->expects(self::once())
                ->method('load')
                ->with($criteria)
                ->willReturn(true);

        $this->assertSame(true, $widget->loadData());
    }

    public function testNewWithoutCriteria()
    {
        /** @var IChartDataSource $chart */
        $chart = $this->getMockForAbstractClass(IChartDataSource::class);

        $widget = new ChartWidget('chart-widget', 'Chart', $chart);

        $this->assertSame('chart-widget', $widget->getName());
        $this->assertSame('Chart', $widget->getLabel());
        $this->assertSame($chart, $widget->getChartDataSource());
        $this->assertSame(null, $widget->getCriteria());
        $this->assertSame(false, $widget->hasCriteria());

        $chart->expects(self::once())
                ->method('load')
                ->with(null)
                ->willReturn(true);

        $this->assertSame(true, $widget->loadData());
    }
}
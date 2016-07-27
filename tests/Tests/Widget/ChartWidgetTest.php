<?php

namespace Dms\Core\Tests\Widget;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Auth\IAuthSystem;
use Dms\Core\Auth\IAuthSystemInPackageContext;
use Dms\Core\Auth\Permission;
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
        $authSystem          = $this->getMockForAbstractClass(IAuthSystemInPackageContext::class);
        $requiredPermissions = ['abc' => Permission::named('abc')];

        $chart      = $this->getMockForAbstractClass(IChartDisplay::class);
        $dataSource = $this->getMockForAbstractClass(IChartDataSource::class);
        $criteria   = $this->getMockForAbstractClass(IChartCriteria::class);

        $widget = new ChartWidget('chart-widget', 'Chart', $authSystem, $requiredPermissions, $chart, $criteria);

        $this->assertSame('chart-widget', $widget->getName());
        $this->assertSame('Chart', $widget->getLabel());
        $this->assertSame($chart, $widget->getChartDisplay());
        $this->assertSame($criteria, $widget->getCriteria());
        $this->assertSame(true, $widget->hasCriteria());

        $chart->expects(self::once())
                ->method('getDataSource')
                ->willReturn($dataSource);

        $dataSource->expects(self::once())
                ->method('load')
                ->with($criteria)
                ->willReturn($mock = $this->getMock(IChartDataTable::class));

        $this->assertSame($mock, $widget->loadData());

        $authSystem->expects(self::exactly(2))
            ->method('isAuthorized')
            ->with($requiredPermissions)
            ->willReturnOnConsecutiveCalls(true, false);
        $this->assertSame(true, $widget->isAuthorized());
        $this->assertSame(false, $widget->isAuthorized());
    }

    public function testNewWithoutCriteria()
    {
        $authSystem          = $this->getMockForAbstractClass(IAuthSystemInPackageContext::class);

        /** @var IChartDisplay $chart */
        $chart      = $this->getMockForAbstractClass(IChartDisplay::class);
        $dataSource = $this->getMockForAbstractClass(IChartDataSource::class);

        $widget = new ChartWidget('chart-widget', 'Chart', $authSystem, [], $chart);

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
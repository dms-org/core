<?php

namespace Iddigital\Cms\Core\Tests\Module\Chart;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Module\Chart\ChartDisplay;
use Iddigital\Cms\Core\Module\Chart\ChartView;
use Iddigital\Cms\Core\Table\Chart\IChartDataSource;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ChartDisplayTest extends CmsTestCase
{
    public function testNewWithNoViews()
    {
        $dataSource = $this->getMockForAbstractClass(IChartDataSource::class);

        $display = new ChartDisplay('name', $dataSource, []);

        $this->assertSame('name', $display->getName());
        $this->assertSame($dataSource, $display->getDataSource());
        $this->assertSame([], $display->getViews());
        $this->assertEquals(ChartView::createDefault(), $display->getDefaultView());
        $this->assertSame(false, $display->hasView('some-name'));

        $this->assertThrows(function () use ($display) {
            $display->getView('some-name');
        }, InvalidArgumentException::class);
    }

    public function testDefaultViewsWithNoDefaultReturnsFirst()
    {
        $dataSource = $this->getMockForAbstractClass(IChartDataSource::class);

        $display = new ChartDisplay('name', $dataSource, [
                $view1 = new ChartView('view-1', 'Label', false),
                $view2 = new ChartView('view-2', 'Label', false),
        ]);


        $this->assertSame(['view-1' => $view1, 'view-2' => $view2], $display->getViews());
        $this->assertSame($view1, $display->getDefaultView());

        $this->assertSame(true, $display->hasView('view-1'));
        $this->assertSame($view1, $display->getView('view-1'));
    }

    public function testDefaultViewsWithDefault()
    {
        $dataSource = $this->getMockForAbstractClass(IChartDataSource::class);

        $display = new ChartDisplay('name', $dataSource, [
                $view1 = new ChartView('view-1', 'Label', false),
                $view2 = new ChartView('view-2', 'Label', true),
        ]);

        $this->assertSame(['view-1' => $view1, 'view-2' => $view2], $display->getViews());
        $this->assertSame($view2, $display->getDefaultView());

        $this->assertSame(true, $display->hasView('view-2'));
        $this->assertSame($view2, $display->getView('view-2'));
    }
}
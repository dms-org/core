<?php

namespace Dms\Core\Tests\Module\Chart;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Module\Chart\ChartView;
use Dms\Core\Table\Chart\IChartCriteria;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ChartViewTest extends CmsTestCase
{
    public function testWithCriteria()
    {
        $criteria = $this->getMockForAbstractClass(IChartCriteria::class);

        $view = new ChartView('name', 'Label', false, $criteria);

        $this->assertSame('name', $view->getName());
        $this->assertSame('Label', $view->getLabel());
        $this->assertSame(false, $view->isDefault());
        $this->assertSame(true, $view->hasCriteria());
        $this->assertSame($criteria, $view->getCriteria());
    }

    public function testWithoutCriteria()
    {
        $view = new ChartView('name', 'Label', true);

        $this->assertSame('name', $view->getName());
        $this->assertSame('Label', $view->getLabel());
        $this->assertSame(true, $view->isDefault());
        $this->assertSame(false, $view->hasCriteria());
        $this->assertSame(null, $view->getCriteria());
    }

    public function testCreateDefault()
    {
        $view = ChartView::createDefault();

        $this->assertSame('default', $view->getName());
        $this->assertSame('Default', $view->getLabel());
        $this->assertSame(true, $view->isDefault());
        $this->assertSame(false, $view->hasCriteria());
        $this->assertSame(null, $view->getCriteria());
    }
}
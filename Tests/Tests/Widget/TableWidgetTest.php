<?php

namespace Iddigital\Cms\Core\Tests\Widget;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Table\Chart\IChartCriteria;
use Iddigital\Cms\Core\Table\Chart\IChartDataSource;
use Iddigital\Cms\Core\Table\IRowCriteria;
use Iddigital\Cms\Core\Table\ITableDataSource;
use Iddigital\Cms\Core\Widget\ChartWidget;
use Iddigital\Cms\Core\Widget\TableWidget;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TableWidgetTest extends CmsTestCase
{
    public function testNewWithCriteria()
    {
        $table = $this->getMockForAbstractClass(ITableDataSource::class);
        $criteria = $this->getMockForAbstractClass(IRowCriteria::class);

        $widget = new TableWidget('table-widget', 'Table', $table, $criteria);

        $this->assertSame('table-widget', $widget->getName());
        $this->assertSame('Table', $widget->getLabel());
        $this->assertSame($table, $widget->getTableDataSource());
        $this->assertSame($criteria, $widget->getCriteria());
        $this->assertSame(true, $widget->hasCriteria());

        $table->expects(self::once())
                ->method('load')
                ->with($criteria)
                ->willReturn(true);

        $this->assertSame(true, $widget->loadData());
    }

    public function testNewWithoutCriteria()
    {
        $table = $this->getMockForAbstractClass(ITableDataSource::class);

        $widget = new TableWidget('table-widget', 'Table', $table);

        $this->assertSame('table-widget', $widget->getName());
        $this->assertSame('Table', $widget->getLabel());
        $this->assertSame($table, $widget->getTableDataSource());
        $this->assertSame(null, $widget->getCriteria());
        $this->assertSame(false, $widget->hasCriteria());

        $table->expects(self::once())
                ->method('load')
                ->with(null)
                ->willReturn(true);

        $this->assertSame(true, $widget->loadData());
    }
}
<?php

namespace Dms\Core\Tests\Widget;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Auth\IAuthSystem;
use Dms\Core\Auth\Permission;
use Dms\Core\Module\ITableDisplay;
use Dms\Core\Table\IDataTable;
use Dms\Core\Table\IRowCriteria;
use Dms\Core\Table\ITableDataSource;
use Dms\Core\Widget\TableWidget;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TableWidgetTest extends CmsTestCase
{
    public function testNewWithCriteria()
    {
        $authSystem          = $this->getMockForAbstractClass(IAuthSystem::class);
        $requiredPermissions = ['abc' => Permission::named('abc')];

        $table      = $this->getMockForAbstractClass(ITableDisplay::class);
        $dataSource = $this->getMockForAbstractClass(ITableDataSource::class);
        $criteria   = $this->getMockForAbstractClass(IRowCriteria::class);

        $widget = new TableWidget('table-widget', 'Table', $authSystem, $requiredPermissions, $table, $criteria);

        $this->assertSame('table-widget', $widget->getName());
        $this->assertSame('Table', $widget->getLabel());
        $this->assertSame($table, $widget->getTableDisplay());
        $this->assertSame($criteria, $widget->getCriteria());
        $this->assertSame(true, $widget->hasCriteria());

        $table->expects(self::once())
                ->method('getDataSource')
                ->willReturn($dataSource);

        $dataSource->expects(self::once())
                ->method('load')
                ->with($criteria)
                ->willReturn($mock = $this->getMock(IDataTable::class));

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
        $authSystem          = $this->getMockForAbstractClass(IAuthSystem::class);

        $table      = $this->getMockForAbstractClass(ITableDisplay::class);
        $dataSource = $this->getMockForAbstractClass(ITableDataSource::class);

        $widget = new TableWidget('table-widget', 'Table', $authSystem, [], $table);

        $this->assertSame('table-widget', $widget->getName());
        $this->assertSame('Table', $widget->getLabel());
        $this->assertSame($table, $widget->getTableDisplay());
        $this->assertSame(null, $widget->getCriteria());
        $this->assertSame(false, $widget->hasCriteria());

        $table->expects(self::once())
                ->method('getDataSource')
                ->willReturn($dataSource);

        $dataSource->expects(self::once())
                ->method('load')
                ->with(null)
                ->willReturn($mock = $this->getMock(IDataTable::class));

        $this->assertSame($mock, $widget->loadData());
    }
}
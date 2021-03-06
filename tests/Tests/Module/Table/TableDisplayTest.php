<?php

namespace Dms\Core\Tests\Module\Table;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Module\Table\TableDisplay;
use Dms\Core\Module\Table\TableView;
use Dms\Core\Table\ITableDataSource;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TableDisplayTest extends CmsTestCase
{
    public function testNewWithNoViews()
    {
        $dataSource = $this->getMockForAbstractClass(ITableDataSource::class);

        $display = new TableDisplay('name', $dataSource, []);

        $this->assertSame('name', $display->getName());
        $this->assertSame($dataSource, $display->getDataSource());
        $this->assertEquals([TableView::createDefault()], array_values($display->getViews()));
        $this->assertEquals(TableView::createDefault(), $display->getDefaultView());
        $this->assertSame(false, $display->hasView('some-name'));

        $this->assertThrows(function () use ($display) {
            $display->getView('some-name');
        }, InvalidArgumentException::class);
    }

    public function testDefaultViewsWithNoDefaultReturnsFirst()
    {
        $dataSource = $this->getMockForAbstractClass(ITableDataSource::class);

        $display = new TableDisplay('name', $dataSource, [
                $view1 = new TableView('view-1', 'Label', false),
                $view2 = new TableView('view-2', 'Label', false),
        ]);


        $this->assertSame(['view-1' => $view1, 'view-2' => $view2], $display->getViews());
        $this->assertSame($view1, $display->getDefaultView());

        $this->assertSame(true, $display->hasView('view-1'));
        $this->assertSame($view1, $display->getView('view-1'));
    }

    public function testDefaultViewsWithDefault()
    {
        $dataSource = $this->getMockForAbstractClass(ITableDataSource::class);

        $display = new TableDisplay('name', $dataSource, [
                $view1 = new TableView('view-1', 'Label', false),
                $view2 = new TableView('view-2', 'Label', true),
        ]);

        $this->assertSame(['view-1' => $view1, 'view-2' => $view2], $display->getViews());
        $this->assertSame($view2, $display->getDefaultView());

        $this->assertSame(true, $display->hasView('view-2'));
        $this->assertSame($view2, $display->getView('view-2'));
    }
}
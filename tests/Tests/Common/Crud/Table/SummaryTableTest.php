<?php

namespace Dms\Core\Tests\Common\Crud\Table;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Common\Crud\Action\Object\IObjectAction;
use Dms\Core\Common\Crud\Action\Table\IReorderAction;
use Dms\Core\Common\Crud\Table\SummaryTable;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Module\Table\TableView;
use Dms\Core\Table\ITableDataSource;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class SummaryTableTest extends CmsTestCase
{
    public function testNewWithNoViews()
    {
        $dataSource = $this->getMockForAbstractClass(ITableDataSource::class);

        $table = new SummaryTable('name', $dataSource, []);

        $this->assertSame('name', $table->getName());
        $this->assertSame($dataSource, $table->getDataSource());
        $this->assertEquals([TableView::createDefault()], array_values($table->getViews()));
        $this->assertSame([], $table->getReorderActions());

        $this->assertThrows(function () use ($table) {
            $table->getReorderAction('non-existent');
        }, InvalidArgumentException::class);
    }

    public function testNewWithReorderAction()
    {
        $dataSource = $this->getMockForAbstractClass(ITableDataSource::class);

        $table = new SummaryTable('name', $dataSource, [
                $view1 = new TableView('view-1', 'Label', true),
        ], [
            'view-1' => $reorderAction = $this->getMockForAbstractClass(IReorderAction::class),
        ]);

        $this->assertSame(['view-1' => $view1], $table->getViews());
        $this->assertSame(['view-1' => $reorderAction], $table->getReorderActions());

        $this->assertSame(['view-1' => $reorderAction], $table->getReorderActions());
        $this->assertSame(true, $table->hasReorderAction('view-1'));
        $this->assertSame(false, $table->hasReorderAction('view-2'));
        $this->assertSame($reorderAction, $table->getReorderAction('view-1'));
    }

    public function testReorderActionWithInvalidViewName()
    {
        $this->setExpectedException(InvalidArgumentException::class);

        new SummaryTable('name', $this->getMockForAbstractClass(ITableDataSource::class), [
                new TableView('view-1', 'Label', true),
        ], [
                'non-existent' => $this->getMockForAbstractClass(IReorderAction::class),
        ]);
    }

    public function testInvalidActionClass()
    {
        $this->setExpectedException(InvalidArgumentException::class);

        new SummaryTable('name', $this->getMockForAbstractClass(ITableDataSource::class), [
                new TableView('view-1', 'Label', true),
        ], [
                'view-1' => $this->getMockForAbstractClass(IObjectAction::class),
        ]);
    }
}
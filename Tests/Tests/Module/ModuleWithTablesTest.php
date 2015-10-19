<?php

namespace Iddigital\Cms\Core\Tests\Module;

use Iddigital\Cms\Core\Auth\IPermission;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Form\Field\Builder\Field;
use Iddigital\Cms\Core\Module\Module;
use Iddigital\Cms\Core\Module\Table\TableDisplay;
use Iddigital\Cms\Core\Module\Table\TableView;
use Iddigital\Cms\Core\Table\Builder\Column;
use Iddigital\Cms\Core\Table\DataSource\ArrayTableDataSource;
use Iddigital\Cms\Core\Table\DataSource\ObjectTableDataSource;
use Iddigital\Cms\Core\Tests\Module\Fixtures\ModuleWithTables;
use Iddigital\Cms\Core\Tests\Module\Mock\MockAuthSystem;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ModuleWithTablesTest extends ModuleTestBase
{

    /**
     * @return Module
     */
    protected function buildModule()
    {
        return new ModuleWithTables(new MockAuthSystem());
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
        return 'test-module-with-tables';
    }

    public function testTableGetters()
    {
        $this->assertSame(true, $this->module->hasTable('array-table'));
        $this->assertSame(true, $this->module->hasTable('object-table'));
        $this->assertSame(false, $this->module->hasTable('foo-table'));

        $this->assertSame('array-table', $this->module->getTable('array-table')->getName());
        $this->assertSame('object-table', $this->module->getTable('object-table')->getName());

        $this->assertInstanceOf(ArrayTableDataSource::class, $this->module->getTable('array-table')->getDataSource());
        $this->assertInstanceOf(ObjectTableDataSource::class, $this->module->getTable('object-table')->getDataSource());

        $this->assertSame(
                ['array-table' => TableDisplay::class, 'object-table' => TableDisplay::class],
                array_map('get_class', $this->module->getTables())
        );

        $this->assertThrows(function () {
            $this->module->getTable('non-existent-table');
        }, InvalidArgumentException::class);
    }

    public function testArrayTable()
    {
        $table = $this->module->getTable('array-table');

        $this->assertSame('array-table', $table->getName());

        $this->assertEquals([
                'col' => Column::from(Field::name('col')->label('Column')->string())
        ], $table->getDataSource()->getStructure()->getColumns());

        $this->assertCount(3, $table->getDataSource()->load()->getSections()[0]->getRows());

        $this->assertEquals(TableView::createDefault(), $table->getDefaultView());
        $this->assertSame([], $table->getViews());
    }

    public function testObjectTable()
    {
        $table      = $this->module->getTable('object-table');
        $dataSource = $table->getDataSource();

        $this->assertSame('object-table', $table->getName());

        $this->assertEquals([
                'id'   => Column::from(Field::name('id')->label('Id')->int()),
                'name' => Column::from(Field::name('name')->label('Name')->string()),
        ], $dataSource->getStructure()->getColumns());

        $this->assertCount(3, $dataSource->load()->getSections()[0]->getRows());

        $this->assertEquals(
                new TableView('default', 'Default', true, $dataSource->criteria()->loadAll()),
                $table->getDefaultView()
        );

        $this->assertEquals(
                new TableView('ordered', 'Ordered', false, $dataSource->criteria()->loadAll()->orderByAsc('name')),
                $table->getView('ordered')
        );

        $this->assertSame(['default', 'ordered'], array_keys($table->getViews()));
    }
}
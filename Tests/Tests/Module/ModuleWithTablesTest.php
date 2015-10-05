<?php

namespace Iddigital\Cms\Core\Tests\Module;

use Iddigital\Cms\Core\Auth\IPermission;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Form\Field\Builder\Field;
use Iddigital\Cms\Core\Module\Module;
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

        $this->assertInstanceOf(ArrayTableDataSource::class, $this->module->getTable('array-table'));
        $this->assertInstanceOf(ObjectTableDataSource::class, $this->module->getTable('object-table'));

        $this->assertSame(
                ['array-table' => ArrayTableDataSource::class, 'object-table' => ObjectTableDataSource::class],
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
        ], $table->getStructure()->getColumns());

        $this->assertCount(3, $table->load()->getSections()[0]->getRows());
    }

    public function testObjectTable()
    {
        $table = $this->module->getTable('object-table');

        $this->assertSame('object-table', $table->getName());
        $this->assertEquals([
                'id' => Column::from(Field::name('id')->label('Id')->int()),
                'name' => Column::from(Field::name('name')->label('Name')->string()),
        ], $table->getStructure()->getColumns());

        $this->assertCount(3, $table->load()->getSections()[0]->getRows());
    }
}
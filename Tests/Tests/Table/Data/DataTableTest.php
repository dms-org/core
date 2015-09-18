<?php

namespace Iddigital\Cms\Core\Tests\Table\Data;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Form\Field\Builder\Field;
use Iddigital\Cms\Core\Table\Builder\Column;
use Iddigital\Cms\Core\Table\Data\DataTable;
use Iddigital\Cms\Core\Table\Data\TableRow;
use Iddigital\Cms\Core\Table\Data\TableSection;
use Iddigital\Cms\Core\Table\TableStructure;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DataTableTest extends CmsTestCase
{
    public function testNewDataTable()
    {
        $structure = new TableStructure([
                Column::from(Field::name('foo')->label('Foo')->string())
        ]);
        $sections  = [new TableSection($structure, null, [new TableRow(['foo' => ['foo' => 'data']])])];

        $table = new DataTable($structure, $sections);

        $this->assertSame($structure, $table->getStructure());
        $this->assertSame($sections, $table->getSections());
    }

    public function testSectionsMustHaveSameStructure()
    {
        $this->setExpectedException(InvalidArgumentException::class);

        $structure = new TableStructure([
                Column::from(Field::name('foo')->label('Foo')->string())
        ]);

        $structure1 = new TableStructure([
                Column::from(Field::name('bar')->label('Bar')->string())
        ]);

        new DataTable($structure, [new TableSection($structure1, null, [new TableRow(['bar' => ['bar' => 'data']])])]);

    }
}
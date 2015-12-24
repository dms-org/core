<?php

namespace Dms\Core\Tests\Table\Builder;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Table\Builder\Column;
use Dms\Core\Table\Builder\Table;
use Dms\Core\Table\TableStructure;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TableStructureBuilderTest extends CmsTestCase
{
    public function testCreateTable()
    {
        $table = Table::create([
                Column::from(Field::name('foo')->label('Bar')->string())
        ]);

        $this->assertEquals(
                new TableStructure([
                        Column::from(Field::name('foo')->label('Bar')->string())
                ]),
                $table
        );
    }

    public function testInvalidColumnValue()
    {
        $this->setExpectedException(InvalidArgumentException::class);

        Table::create([
                new \DateTime()
        ]);
    }
}
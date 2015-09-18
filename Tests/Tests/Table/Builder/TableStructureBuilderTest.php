<?php

namespace Iddigital\Cms\Core\Tests\Table\Builder;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Form\Field\Builder\Field;
use Iddigital\Cms\Core\Table\Builder\Column;
use Iddigital\Cms\Core\Table\Builder\Table;
use Iddigital\Cms\Core\Table\TableStructure;

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
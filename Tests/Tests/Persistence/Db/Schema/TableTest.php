<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Schema;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Persistence\Db\Schema\Column;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;
use Iddigital\Cms\Core\Persistence\Db\Schema\Type\Integer;
use Iddigital\Cms\Core\Persistence\Db\Schema\Type\Varchar;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TableTest extends CmsTestCase
{
    public function testGetters()
    {
        $table = new Table('table', [$id = $this->idColumn(), $data = new Column('data', new Varchar(255))]);

        $this->assertSame(['id' => $id, 'data' => $data], $table->getColumns());
        $this->assertSame('table', $table->getName());
        $this->assertSame('id', $table->getPrimaryKeyColumnName());
        $this->assertSame($id, $table->getPrimaryKeyColumn());
        $this->assertSame($id, $table->getColumn('id'));
        $this->assertTrue($table->hasColumn('data'));
        $this->assertSame($data, $table->getColumn('data'));
        $this->assertFalse($table->hasColumn('foo'));
        $this->assertSame(null, $table->getColumn('foo'));
    }

    /**
     * @return Column
     */
    private function idColumn()
    {
        return new Column('id', Integer::normal()->autoIncrement(), true);
    }
}
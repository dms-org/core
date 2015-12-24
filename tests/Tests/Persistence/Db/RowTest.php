<?php

namespace Dms\Core\Tests\Persistence\Db;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Persistence\Db\Row;
use Dms\Core\Persistence\Db\Schema\Column;
use Dms\Core\Persistence\Db\Schema\Table;
use Dms\Core\Persistence\Db\Schema\Type\Integer;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class RowTest extends CmsTestCase
{
    private function table()
    {
        return new Table('table', [new Column('id', Integer::normal()->autoIncrement(), true)]);
    }

    public function testWithId()
    {
        $rows = new Row($table = $this->table(), ['id' => 1]);

        $this->assertSame(1, $rows->getPrimaryKey());
        $this->assertSame(true, $rows->hasPrimaryKey());
        $this->assertSame(true, $rows->hasColumn('id'));
        $this->assertSame(false, $rows->hasColumn('abc'));
        $this->assertSame(['id' => 1], $rows->getColumnData());
        $this->assertSame($table, $rows->getTable());
        $this->assertSame('id', $rows->getPrimaryKeyColumn());
    }

    public function testWithoutId()
    {
        $rows = new Row($table = $this->table(), ['id' => null]);

        $this->assertSame(null, $rows->getPrimaryKey());
        $this->assertSame(false, $rows->hasPrimaryKey());
        $this->assertSame(false, $rows->hasColumn('id'));
        $this->assertSame(false, $rows->hasColumn('abc'));
        $this->assertSame(['id' => null], $rows->getColumnData());
        $this->assertSame($table, $rows->getTable());
        $this->assertSame('id', $rows->getPrimaryKeyColumn());
    }

    public function testWithoutPrimaryKeyColumn()
    {
        $rows = new Row($table = new Table('table', []), ['id' => 1]);

        $this->assertSame(null, $rows->getPrimaryKey());
        $this->assertSame(false, $rows->hasPrimaryKey());
        $this->assertSame(['id' => 1], $rows->getColumnData());
        $this->assertSame($table, $rows->getTable());
        $this->assertSame(null, $rows->getPrimaryKeyColumn());
    }

    public function testPrimaryKeyCallbacks()
    {
        $rows = new Row($this->table(), ['id' => null]);

        $i = 0;
        $rows->onInsertPrimaryKey(function ($p) use (&$i) {
            $i += $p;
        });
        $rows->onInsertPrimaryKey(function ($p) use (&$i) {
            $i += $p;
        });


        $rows->firePrimaryKeyCallbacks(10);

        $this->assertSame(true, $rows->hasPrimaryKey());
        $this->assertSame(10, $rows->getPrimaryKey());
        $this->assertSame(20, $i, 'Callbacks must be fired with primary key');
    }

    public function testLockingData()
    {
        $row = new Row($this->table(), ['id' => null], ['id' => 1]);

        $this->assertSame(['id' => 1], $row->getLockingColumnData());

        $row->setLockingColumn('id', 5);

        $this->assertSame(['id' => 5], $row->getLockingColumnData());
    }
}
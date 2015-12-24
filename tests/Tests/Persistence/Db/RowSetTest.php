<?php

namespace Dms\Core\Tests\Persistence\Db;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Persistence\Db\Row;
use Dms\Core\Persistence\Db\RowSet;
use Dms\Core\Persistence\Db\Schema\Column;
use Dms\Core\Persistence\Db\Schema\Table;
use Dms\Core\Persistence\Db\Schema\Type\Integer;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class RowSetTest extends CmsTestCase
{
    private function table()
    {
        return new Table('table', [new Column('id', Integer::normal()->autoIncrement(), true)]);
    }

    public function testFromRows()
    {
        $rows = RowSet::fromRowArray($this->table(), [['id' => 1], ['id' => 2]]);

        $this->assertEquals([$rows->createRow(['id' => 1]), $rows->createRow(['id' => 2])], $rows->getRows());
    }

    public function testFromRowWithoutPrimaryKeys()
    {
        $rows = RowSet::fromRowArray($this->table(), [['id' => null], ['id' => null]]);

        $this->assertEquals([$rows->createRow(['id' => null]), $rows->createRow(['id' => null])], $rows->getRows());
    }

    public function testGetters()
    {
        $rows = new RowSet($table = $this->table());

        $this->assertSame([], $rows->getRows());
        $this->assertSame([], $rows->getRowsWithoutPrimaryKeys()->getRows());
        $this->assertSame([], $rows->getRowsWithPrimaryKeys()->getRows());
        $this->assertSame($table, $rows->getTable());
        $this->assertSame([], $rows->getPrimaryKeys());
        $this->assertSame(0, $rows->count());
    }

    public function testAdd()
    {
        $rows = new RowSet($this->table());

        $this->assertTrue($rows->add($rows->createRow(['id' => 1])));
        $this->assertTrue($rows->add($rows->createRow(['id' => 2])));
        $this->assertFalse($rows->add($rows->createRow(['id' => 1])));
        $this->assertEquals([$rows->createRow(['id' => 1]), $rows->createRow(['id' => 2])], $rows->getRowsWithPrimaryKeys()->getRows());
        $this->assertEquals([], $rows->getRowsWithoutPrimaryKeys()->getRows());
        $this->assertSame(2, $rows->count());
    }

    public function testHas()
    {
        $rows = new RowSet($this->table());

        $this->assertFalse($rows->has(1));
        $this->assertTrue($rows->add($rows->createRow(['id' => 1])));
        $this->assertTrue($rows->add($rows->createRow(['id' => 2])));
        $this->assertTrue($rows->has(1));
        $this->assertTrue($rows->has(2));
    }

    public function testRemove()
    {
        $rows = new RowSet($this->table());

        $this->assertFalse($rows->remove(1));
        $this->assertTrue($rows->add($rows->createRow(['id' => 1])));
        $this->assertTrue($rows->add($rows->createRow(['id' => 2])));
        $this->assertTrue($rows->remove(1));
        $this->assertEquals([$rows->createRow(['id' => 2])], $rows->getRowsWithPrimaryKeys()->getRows());
        $this->assertSame([2], $rows->getPrimaryKeys());
    }

    public function testRowsWithoutPrimaryKey()
    {
        $rows = new RowSet($this->table());

        $this->assertTrue($rows->add($rows->createRow(['id' => null])));
        $this->assertTrue($rows->add($rows->createRow(['id' => null])));

        $this->assertEquals([$rows->createRow(['id' => null]), $rows->createRow(['id' => null])], $rows->getRowsWithoutPrimaryKeys()->getRows());
    }

    public function testThrowsOnWrongTableRow()
    {
        $this->setExpectedException(InvalidArgumentException::class);

        $rows = new RowSet($this->table());
        $rows->add(new Row(new Table('other_table', []), ['id' => null]));
    }

    public function testGetFirstRowOrNull()
    {
        $rows = new RowSet($this->table());

        $this->assertSame(null, $rows->getFirstRowOrNull());

        $rows = RowSet::fromRowArray($this->table(), [['id' => null]]);

        $this->assertSame(['id' => null], $rows->getFirstRowOrNull()->getColumnData());

        $rows = RowSet::fromRowArray($this->table(), [['id' => 1]]);

        $this->assertSame(['id' => 1], $rows->getFirstRowOrNull()->getColumnData());
    }
}
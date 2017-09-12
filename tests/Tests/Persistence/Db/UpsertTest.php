<?php

namespace Dms\Core\Tests\Persistence\Db;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Persistence\Db\Query\Upsert;
use Dms\Core\Persistence\Db\RowSet;
use Dms\Core\Persistence\Db\Schema\Column;
use Dms\Core\Persistence\Db\Schema\PrimaryKeyBuilder;
use Dms\Core\Persistence\Db\Schema\Table;
use Dms\Core\Persistence\Db\Schema\Type\Integer;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class UpsertTest extends CmsTestCase
{
    private function table()
    {
        return new Table('table', [PrimaryKeyBuilder::incrementingInt('id')]);
    }

    public function testNew()
    {
        $rowSet = new RowSet($this->table());
        $rowSet->add($rowSet->createRow(['id' => null]));
        $rowSet->add($rowSet->createRow(['id' => 1]));
        $upsert = new Upsert($rowSet, ['id']);

        $this->assertEquals(new RowSet($this->table(), [$rowSet->createRow(['id' => null])]), $upsert->getRowsWithoutPrimaryKeys());
        $this->assertEquals(new RowSet($this->table(), [$rowSet->createRow(['id' => 1])]), $upsert->getRowsWithPrimaryKeys());
        $this->assertSame($rowSet->getTable(), $upsert->getTable());
        $this->assertSame(['id'], $upsert->getLockingColumnNames());
    }

    public function testInvalidLockingColumn()
    {
        $this->expectException(InvalidArgumentException::class);

        new Upsert(new RowSet($this->table()), ['non_existent_column']);
    }
}
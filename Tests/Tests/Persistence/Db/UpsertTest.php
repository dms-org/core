<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Persistence\Db\Query\Upsert;
use Iddigital\Cms\Core\Persistence\Db\RowSet;
use Iddigital\Cms\Core\Persistence\Db\Schema\Column;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;
use Iddigital\Cms\Core\Persistence\Db\Schema\Type\Integer;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class UpsertTest extends CmsTestCase
{
    private function table()
    {
        return new Table('table', [new Column('id', Integer::normal()->autoIncrement(), true)]);
    }

    public function testNew()
    {
        $rowSet = new RowSet($this->table());
        $upsert = new Upsert($rowSet, ['id']);

        $this->assertSame($rowSet, $upsert->getRows());
        $this->assertSame($rowSet->getTable(), $upsert->getTable());
        $this->assertSame(['id'], $upsert->getLockingColumnNames());
    }

    public function testInvalidLockingColumn()
    {
        $this->setExpectedException(InvalidArgumentException::class);

        new Upsert(new RowSet($this->table()), ['non_existent_column']);
    }
}
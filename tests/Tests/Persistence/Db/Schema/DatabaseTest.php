<?php

namespace Dms\Core\Tests\Persistence\Db\Schema;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Persistence\Db\Schema\Column;
use Dms\Core\Persistence\Db\Schema\Database;
use Dms\Core\Persistence\Db\Schema\ForeignKey;
use Dms\Core\Persistence\Db\Schema\ForeignKeyMode;
use Dms\Core\Persistence\Db\Schema\Index;
use Dms\Core\Persistence\Db\Schema\Table;
use Dms\Core\Persistence\Db\Schema\Type\Integer;
use Dms\Core\Persistence\Db\Schema\Type\Varchar;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DatabaseTest extends CmsTestCase
{
    /**
     * @param string $name
     *
     * @return Table
     */
    private function getTestTable($name)
    {
        return new Table($name, [new Column('data', new Varchar(255))]);
    }

    public function testNewDatabase()
    {
        $db = new Database([$table = $this->getTestTable('table')]);

        $this->assertSame(['table' => $table], $db->getTables());
        $this->assertSame(['table'], $db->getTableNames());
        $this->assertSame(true, $db->hasTable('table'));
        $this->assertSame($table, $db->getTable('table'));
        $this->assertSame(false, $db->hasTable('non_existent'));

        $this->assertThrows(function () use ($db) {
            $db->getTable('non_existent');
        }, InvalidArgumentException::class);
    }

    public function testThrowsExceptionForDuplicateTableName()
    {
        $this->expectException(InvalidArgumentException::class);

        new Database([$this->getTestTable('table'), $this->getTestTable('table')]);
    }
}
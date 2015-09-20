<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Schema;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Persistence\Db\Schema\Column;
use Iddigital\Cms\Core\Persistence\Db\Schema\Database;
use Iddigital\Cms\Core\Persistence\Db\Schema\ForeignKey;
use Iddigital\Cms\Core\Persistence\Db\Schema\ForeignKeyMode;
use Iddigital\Cms\Core\Persistence\Db\Schema\Index;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;
use Iddigital\Cms\Core\Persistence\Db\Schema\Type\Integer;
use Iddigital\Cms\Core\Persistence\Db\Schema\Type\Varchar;

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
        $this->setExpectedException(InvalidArgumentException::class);

        new Database([$this->getTestTable('table'), $this->getTestTable('table')]);
    }
}
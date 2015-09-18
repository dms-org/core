<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Persistence\Db\Schema\Column;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;
use Iddigital\Cms\Core\Persistence\Db\Schema\Type\Integer;
use Iddigital\Cms\Core\Persistence\Db\Schema\Type\Varchar;
use Iddigital\Cms\Core\Tests\Persistence\Db\Mock\DuplicateKeyException;
use Iddigital\Cms\Core\Tests\Persistence\Db\Mock\ForeignKeyConstraintException;
use Iddigital\Cms\Core\Tests\Persistence\Db\Mock\MockDatabase;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MockDatabaseTest extends CmsTestCase
{

    /**
     * @var MockDatabase
     */
    protected $db;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->db = new MockDatabase();
    }

    public function testCreateTable()
    {
        $structure = new Table('foo', [new Column('id', Integer::normal(), true)]);
        $table     = $this->db->createTable($structure);

        $this->assertTrue($this->db->hasTable('foo'));
        $this->assertSame($table, $this->db->getTable('foo'));
        $this->assertSame('foo', $table->getName());
        $this->assertSame($structure, $table->getStructure());
        $this->assertSame([], $table->getRows());
        $this->assertSame([], $table->getForeignKeys());
        $this->assertSame([], $table->getColumnData('id'));
    }

    public function testGetColumn()
    {
        $column    = new Column('id', Integer::normal(), true);
        $structure = new Table('foo', [$column]);
        $this->db->createTable($structure);

        $this->assertTrue($this->db->hasColumn('foo.id'));
        $this->assertSame($column,  $this->db->getColumn('foo.id'));
        $this->assertNull($this->db->getColumn('foo.other'));
    }

    public function testTableInsertValid()
    {
        $table = $this->db->createTable(new Table('foo', [new Column('id', Integer::normal(), true)]));

        $this->assertSame(1, $table->insert(['id' => null]));
        $this->assertSame(2, $table->insert(['id' => null]));
        $this->assertSame(5, $table->insert(['id' => 5]));
        $this->assertSame(6, $table->insert(['id' => null]));
    }

    public function testTableInsertDuplicateKey()
    {
        $this->setExpectedException(DuplicateKeyException::class);
        $table = $this->db->createTable(new Table('foo', [new Column('id', Integer::normal(), true)]));

        $table->insert(['id' => 1]);
        $table->insert(['id' => 1]);
    }

    public function testTableInsertInvalidRow()
    {
        $table = $this->db->createTable(new Table('foo', [new Column('id', Integer::normal(), true)]));

        $this->assertThrows(function () use ($table) {
            $table->insert([]);
        }, InvalidArgumentException::class);

        $this->assertThrows(function () use ($table) {
            $table->insert(['foo' => 'bar']);
        }, InvalidArgumentException::class);

        $this->assertThrows(function () use ($table) {
            $table->insert(['id' => null, 'foo' => 'bar']);
        }, InvalidArgumentException::class);
    }

    public function testHasPrimaryKey()
    {
        $table = $this->db->createTable(new Table('foo', [new Column('id', Integer::normal(), true)]));

        $this->assertFalse($table->hasRowWithPrimaryKey(1));
        $table->insert(['id' => 1]);
        $this->assertTrue($table->hasRowWithPrimaryKey(1));
    }

    public function testUpdateValidRow()
    {
        $table = $this->db->createTable(new Table('foo', [
                new Column('id', Integer::normal(), true),
                new Column('data', new Varchar(255))
        ]));

        $this->assertFalse($table->update(1, ['id' => 1, 'data' => 'bar']));
        $table->insert(['id' => 1, 'data' => 'foo']);
        $this->assertTrue($table->update(1, ['id' => 1, 'data' => 'bar']));
        $this->assertSame([1 => ['id' => 1, 'data' => 'bar']], $table->getRows());
    }

    public function testUpdateColumns()
    {
        $table = $this->db->createTable(new Table('foo', [
                new Column('id', Integer::normal(), true),
                new Column('data', new Varchar(255))
        ]));

        $this->assertFalse($table->updateColumns(1, ['data' => 'bar']));
        $table->insert(['id' => 1, 'data' => 'foo']);
        $this->assertTrue($table->updateColumns(1, ['data' => 'bar']));
        $this->assertSame([1 => ['id' => 1, 'data' => 'bar']], $table->getRows());
    }

    public function testAddForeignKey()
    {
        $foo = $this->db->createTable(new Table('foo', [new Column('id', Integer::normal(), true)]));
        $bar = $this->db->createTable(new Table('bar', [new Column('id', Integer::normal(), true), new Column('foreign', Integer::normal()->nullable())]));

        $this->db->createForeignKey('bar.foreign', 'foo.id');
        $foo->insert(['id' => 1]);
        $bar->insert(['id' => null, 'foreign' => 1]);
        // Allow null foreign keys
        $bar->insert(['id' => null, 'foreign' => null]);
        $bar->validateConstraints();

        $this->setExpectedException(ForeignKeyConstraintException::class);
        $bar->insert(['id' => null, 'foreign' => 2]);
        $bar->validateConstraints();
    }
}
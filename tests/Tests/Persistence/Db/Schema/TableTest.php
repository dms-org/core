<?php

namespace Dms\Core\Tests\Persistence\Db\Schema;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Persistence\Db\Schema\Column;
use Dms\Core\Persistence\Db\Schema\ForeignKey;
use Dms\Core\Persistence\Db\Schema\ForeignKeyMode;
use Dms\Core\Persistence\Db\Schema\Index;
use Dms\Core\Persistence\Db\Schema\Table;
use Dms\Core\Persistence\Db\Schema\Type\Integer;
use Dms\Core\Persistence\Db\Schema\Type\Varchar;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TableTest extends CmsTestCase
{
    /**
     * @return Column
     */
    private function idColumn()
    {
        return new Column('id', Integer::normal()->autoIncrement(), true);
    }

    public function testGetters()
    {
        $table = new Table('table', [$id = $this->idColumn(), $data = new Column('data', new Varchar(255))]);

        $this->assertSame(['id' => $id, 'data' => $data], $table->getColumns());
        $this->assertSame('table', $table->getName());
        $this->assertSame('id', $table->getPrimaryKeyColumnName());
        $this->assertSame($id, $table->getPrimaryKeyColumn());
        $this->assertSame($id, $table->findColumn('id'));
        $this->assertTrue($table->hasColumn('data'));
        $this->assertSame($data, $table->findColumn('data'));
        $this->assertFalse($table->hasColumn('foo'));
        $this->assertSame(null, $table->findColumn('foo'));

        $this->assertSame([], $table->getIndexes());
        $this->assertSame([], $table->getForeignKeys());
    }

    public function testWithPrefix()
    {
        $table = new Table(
                'table',
                [$id = $this->idColumn(), $data = new Column('data', new Varchar(255))],
                [$index = new Index('data_index', false, ['data'])],
                [$fk = new ForeignKey('id_fk', ['id'], 'other_table', ['fk'], ForeignKeyMode::CASCADE, ForeignKeyMode::CASCADE)]
        );

        $this->assertSame(['data_index' => $index], $table->getIndexes());
        $this->assertSame(['id_fk' => $fk], $table->getForeignKeys());

        $prefixed = $table->withPrefix('foo_');

        $this->assertEquals(['foo_id' => $id->withPrefix('foo_'), 'foo_data' => $data->withPrefix('foo_')], $prefixed->getColumns());
        $this->assertSame('foo_table', $prefixed->getName());
        $this->assertSame('foo_id', $prefixed->getPrimaryKeyColumnName());
        $this->assertFalse($prefixed->hasColumn('data'));

        $this->assertEquals(['foo_data_index' => $index->withPrefix('foo_')], $prefixed->getIndexes());
        $this->assertEquals(['foo_id_fk' => $fk->withPrefix('foo_')], $prefixed->getForeignKeys());
    }

    public function testInvalidIndexColumn()
    {
        $this->setExpectedException(InvalidArgumentException::class);

        new Table(
                'table',
                [$this->idColumn(), new Column('data', new Varchar(255))],
                [new Index('bad_index', false, ['some_random_column'])],
                []
        );
    }

    public function testInvalidForeignKeyColumn()
    {
        $this->setExpectedException(InvalidArgumentException::class);

        new Table(
                'table',
                [$this->idColumn(), new Column('data', new Varchar(255))],
                [],
                [new ForeignKey('bad_fk', ['some_random_column'], 'other_table', ['fk'], ForeignKeyMode::CASCADE, ForeignKeyMode::CASCADE)]
        );
    }

    public function testWithColumnsIgnoringConstraints()
    {
        $table = (new Table(
                'table',
                [$id = $this->idColumn(), $data = new Column('data', new Varchar(255))],
                [new Index('index', false, ['data'])],
                [new ForeignKey('fk', ['id'], 'other_table', ['fk'], ForeignKeyMode::CASCADE, ForeignKeyMode::CASCADE)]
        ))->withColumnsButIgnoringConstraints([$id]);

        $this->assertEquals(
                new Table('table', [$id]),
                $table
        );
    }
}
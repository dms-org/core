<?php

namespace Dms\Core\Tests\Persistence\Db\Doctrine;

use Dms\Core\Persistence\Db\Schema\PrimaryKeyBuilder;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Dms\Core\Exception\InvalidOperationException;
use Dms\Core\Persistence\Db\Doctrine\DoctrineConnection;
use Dms\Core\Persistence\Db\Doctrine\DoctrinePlatform;
use Dms\Core\Persistence\Db\Query\BulkUpdate;
use Dms\Core\Persistence\Db\Query\Delete;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Persistence\Db\Query\Select;
use Dms\Core\Persistence\Db\Query\Update;
use Dms\Core\Persistence\Db\Query\Upsert;
use Dms\Core\Persistence\Db\RowSet;
use Dms\Core\Persistence\Db\Schema\Column;
use Dms\Core\Persistence\Db\Schema\Table;
use Dms\Core\Persistence\Db\Schema\Type\Integer;
use Dms\Core\Persistence\Db\Schema\Type\Text;
use Dms\Core\Persistence\Db\Schema\Type\Varchar;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DoctrineConnectionTest extends DoctrineTestBase
{
    /**
     * @var DoctrineConnection
     */
    protected $connection;

    public function setUp(): void
    {
        try {
            $this->connection = new DoctrineConnection(DriverManager::getConnection([
                    'url' => 'sqlite://:memory:',
            ]));
        } catch (\Exception $e) {
            $this->markTestSkipped('Sqlite must be enabled: ' . $e->getMessage());
        }
    }

    public function testNewConnection()
    {
        /** @var DoctrinePlatform $platform */
        $platform = $this->connection->getPlatform();
        $this->assertInstanceOf(DoctrinePlatform::class, $platform);
        $this->assertInstanceOf(SqlitePlatform::class, $platform->getDoctrinePlatform());
    }

    public function testTransaction()
    {
        $this->assertFalse($this->connection->isInTransaction());

        $this->connection->beginTransaction();
        $this->assertTrue($this->connection->isInTransaction());

        $this->connection->rollbackTransaction();
        $this->assertFalse($this->connection->isInTransaction());

        $this->connection->beginTransaction();
        $this->assertTrue($this->connection->isInTransaction());

        $this->connection->commitTransaction();
        $this->assertFalse($this->connection->isInTransaction());
    }

    public function testWithinTransaction()
    {
        $this->assertFalse($this->connection->isInTransaction());

        $this->connection->withinTransaction(function () {
            $this->assertTrue($this->connection->isInTransaction());
        });

        $this->assertFalse($this->connection->isInTransaction());
    }

    public function testWithinTransactionInParentTransaction()
    {
        $this->connection->beginTransaction();
        $this->assertTrue($this->connection->isInTransaction());

        $this->connection->withinTransaction(function () {
            $this->assertTrue($this->connection->isInTransaction());
        });

        $this->assertTrue($this->connection->isInTransaction());

        $this->connection->commitTransaction();
        $this->assertFalse($this->connection->isInTransaction());
    }

    public function testWithinTransactionWithException()
    {
        $this->assertFalse($this->connection->isInTransaction());

        $exception = new \Exception;
        try {
            $this->connection->withinTransaction(function () use ($exception) {
                $this->assertTrue($this->connection->isInTransaction());
                throw $exception;
            });
        } catch (\Exception $caught) {
            $this->assertSame($exception, $caught);
        }

        $this->assertFalse($this->connection->isInTransaction());
    }

    protected function setUpDummyTable()
    {
        $this->connection->prepare('CREATE TABLE foo (id INTEGER PRIMARY KEY, data VARCHAR(255))')->execute();

        return new Table('foo', [
                PrimaryKeyBuilder::incrementingInt('id'),
                new Column('data', new Varchar(255)),
        ]);
    }

    public function testMustExecuteQueryBeforeLoadingResults()
    {
        $this->setUpDummyTable();

        $this->assertThrows(function () {
            $this->connection->prepare('SELECT * FROM foo')->getResults();
        }, InvalidOperationException::class);

        $this->assertThrows(function () {
            $this->connection->prepare('SELECT * FROM foo')->getAffectedRows();
        }, InvalidOperationException::class);

        $this->connection->prepare('SELECT * FROM foo')->execute()->getResults();
    }

    public function testLastInsertId()
    {
        $this->setUpDummyTable();

        $this->connection->prepare('INSERT INTO foo VALUES (?, ?)')
                ->execute([1 => 1, 2 => 'foo']);

        $this->assertSame(1, $this->connection->getLastInsertId());

        $this->connection->prepare('INSERT INTO foo VALUES (?, ?)')
                ->execute([1 => null, 2 => 'foo']);

        $this->assertSame(2, $this->connection->getLastInsertId());
    }

    public function testAffectedRows()
    {
        $this->setUpDummyTable();

        $this->connection->prepare('INSERT INTO foo VALUES (?, ?)')
                ->execute([1 => 1, 2 => 'foo']);

        $this->assertSame(
                1,
                $this->connection
                        ->prepare('UPDATE foo SET data = ?')
                        ->setParameter(1, 'bar')
                        ->execute()
                        ->getAffectedRows()
        );
    }

    public function testGetResults()
    {
        $this->setUpDummyTable();

        $this->connection->prepare('INSERT INTO foo VALUES (NULL, ?)')
                ->execute([1 => 'foo'])
                ->execute([1 => 'bar'])
                ->execute([1 => 'baz']);

        $this->assertEquals(
                [
                        ['id' => 1, 'data' => 'foo'],
                        ['id' => 2, 'data' => 'bar'],
                        ['id' => 3, 'data' => 'baz'],
                ],
                $this->connection
                        ->prepare('SELECT * FROM foo')
                        ->execute()
                        ->getResults()
        );
    }

    public function testSelect()
    {
        $table = $this->setUpDummyTable();

        $this->connection->prepare('INSERT INTO foo VALUES (NULL, ?)')
                ->execute([1 => 'foo']);

        $this->assertEquals(
                [
                        ['id' => 1, 'data' => 'foo'],
                ],
                $this->connection->load(Select::allFrom($table))->asArray()
        );
    }

    public function testUpdate()
    {
        $table = $this->setUpDummyTable();

        $this->connection->prepare('INSERT INTO foo VALUES (NULL, ?)')
                ->execute([1 => 'foo']);

        $this->assertSame(1, $this->connection->update(
                Update::from($table)
                        ->set('data', Expr::param(Text::long(), 'bar'))
        ));

        $this->assertEquals(
                [
                        ['id' => 1, 'data' => 'bar'],
                ],
                $this->connection->prepare('SELECT * FROM foo')->execute()->getResults()
        );
    }

    public function testDelete()
    {
        $table = $this->setUpDummyTable();

        $this->connection->prepare('INSERT INTO foo VALUES (NULL, ?)')
                ->execute([1 => 'foo']);

        $this->assertSame(1, $this->connection->delete(Delete::from($table)));

        $this->assertEquals(
                [],
                $this->connection->prepare('SELECT * FROM foo')->execute()->getResults()
        );
    }

    public function testUpsert()
    {
        $table = $this->setUpDummyTable();

        $this->connection->prepare('INSERT INTO foo VALUES (NULL, ?)')
                ->execute([1 => 'abc']);

        $this->connection->upsert(new Upsert(RowSet::fromRowArray(
                $table,
                [
                        ['id' => null, 'data' => 'foo'],
                        ['id' => null, 'data' => 'bar'],
                        ['id' => null, 'data' => 'baz'],
                        ['id' => 1, 'data' => 'abc123'],
                ]
        )));

        $this->assertEquals(
                [
                        ['id' => 1, 'data' => 'abc123'],
                        ['id' => 2, 'data' => 'foo'],
                        ['id' => 3, 'data' => 'bar'],
                        ['id' => 4, 'data' => 'baz'],
                ],
                $this->connection->prepare('SELECT * FROM foo')->execute()->getResults()
        );
    }

    public function testBulkUpdate()
    {
        $table = $this->setUpDummyTable();

        $this->connection->prepare('INSERT INTO foo VALUES (NULL, ?)')
                ->execute([1 => 'abc'])
                ->execute([1 => '123'])
                ->execute([1 => 'foo']);

        $this->connection->bulkUpdate(new BulkUpdate(RowSet::fromRowArray(
                $table,
                [
                        ['id' => 1, 'data' => 'foo'],
                        ['id' => 3, 'data' => 'baz'],
                ]
        )));

        $this->assertEquals(
                [
                        ['id' => 1, 'data' => 'foo'],
                        ['id' => 2, 'data' => '123'],
                        ['id' => 3, 'data' => 'baz'],
                ],
                $this->connection->prepare('SELECT * FROM foo')->execute()->getResults()
        );
    }
}
<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping;

use Dms\Core\Persistence\Db\Mapping\IEntityMapper;
use Dms\Core\Persistence\Db\Mapping\IOrm;
use Dms\Core\Persistence\Db\Query\IQuery;
use Dms\Core\Persistence\Db\Schema\Table;
use Dms\Core\Persistence\DbRepository;
use Dms\Core\Tests\Persistence\Db\Mock\MockConnection;
use Dms\Core\Tests\Persistence\Db\Mock\MockDatabase;
use Dms\Core\Tests\Persistence\Db\Mock\MockTable;
use Dms\Core\Tests\Persistence\Db\MockDatabaseTestBase;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class DbIntegrationTest extends MockDatabaseTestBase
{
    /**
     * @var MockDatabase
     */
    protected $db;

    /**
     * @var IOrm
     */
    protected $orm;

    /**
     * @var MockTable
     */
    protected $table;

    /**
     * @var MockConnection
     */
    protected $connection;

    /**
     * @var DbRepository
     */
    protected $repo;

    /**
     * @var IEntityMapper
     */
    protected $mapper;

    public function setUp(): void
    {
        $this->db     = new MockDatabase();
        $this->orm    = $this->loadOrm();
        $this->mapper = $this->orm->getEntityMapper($this->mapperAndRepoType());
        $this->buildDatabase($this->db, $this->orm);
        $this->connection = new MockConnection($this->db);
        $this->repo       = new DbRepository($this->connection, $this->mapper);
        $this->table      = $this->db->getTable($this->mapper->getPrimaryTableName());
    }

    /**
     * @return IOrm
     */
    abstract protected function loadOrm();

    /**
     * @return string
     */
    protected function mapperAndRepoType()
    {
        $entityMappers = $this->orm->getEntityMappers();
        /** @var IEntityMapper $mapper */
        $mapper = reset($entityMappers);

        return $mapper->getObjectType();
    }

    /**
     * @param MockDatabase $db
     * @param IOrm         $orm
     *
     * @return void
     */
    protected function buildDatabase(MockDatabase $db, IOrm $orm)
    {
        foreach ($orm->getDatabase()->getTables() as $table) {
            $db->createTable($table);
        }

        $db->loadForeignKeys();
    }

    /**
     * @param string $table
     *
     * @return Table
     */
    protected function getSchemaTable($table)
    {
        return $this->db->getTable($table)->getStructure();
    }

    /**
     * @param array $reasonQueryTypeMap
     *
     * @return void
     */
    protected function assertExecutedQueryTypes(array $reasonQueryTypeMap)
    {
        $this->assertSame(array_values($reasonQueryTypeMap), array_map('get_class', $this->connection->getQueryLog()));
    }

    /**
     * @param IQuery[] $queries
     * @param string   $message
     *
     * @return void
     */
    protected function assertExecutedQueries(array $queries, $message = '')
    {
        $this->assertEquals(array_values($queries), $this->connection->getQueryLog(), $message);
    }

    /**
     * @param int    $number
     * @param IQuery $query
     * @param string $message
     *
     * @return void
     */
    protected function assertExecutedQueryNumber($number, IQuery $query, $message = '')
    {
        $this->assertEquals($query, $this->connection->getQueryLog()[$number - 1], $message);
    }
}
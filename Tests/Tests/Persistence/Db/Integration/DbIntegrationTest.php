<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration;

use Iddigital\Cms\Core\Persistence\Db\Mapping\IEntityMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IOrm;
use Iddigital\Cms\Core\Persistence\Db\Query\Query;
use Iddigital\Cms\Core\Persistence\DbRepository;
use Iddigital\Cms\Core\Tests\Persistence\Db\Mock\MockConnection;
use Iddigital\Cms\Core\Tests\Persistence\Db\Mock\MockDatabase;
use Iddigital\Cms\Core\Tests\Persistence\Db\Mock\MockTable;
use Iddigital\Cms\Core\Tests\Persistence\Db\MockDatabaseTestBase;

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

    public function setUp()
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
     * @param MockDatabase  $db
     * @param IOrm $orm
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
     * @return \Iddigital\Cms\Core\Persistence\Db\Schema\Table
     */
    protected function getSchemaTable($table)
    {
        return $this->db->getTable($table)->getStructure();
    }

    protected function assertExecutedQueryTypes(array $reasonQueryTypeMap)
    {
        $this->assertSame(array_values($reasonQueryTypeMap), array_map('get_class', $this->connection->getQueryLog()));
    }

    protected function assertExecutedQueries(array $queries, $message = '')
    {
        $this->assertEquals(array_values($queries), $this->connection->getQueryLog(), $message);
    }

    protected function assertExecutedQueryNumber($number, Query $query, $message = '')
    {
        $this->assertEquals($query, $this->connection->getQueryLog()[$number - 1], $message);
    }
}
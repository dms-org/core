<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel;

use Dms\Core\Persistence\Db\Connection\IConnection;
use Dms\Core\Persistence\Db\Mapping\IOrm;
use Dms\Core\Persistence\Db\Mapping\ReadModel\ReadModelMapper;
use Dms\Core\Persistence\ReadModelRepository;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\DbIntegrationTest;
use Dms\Core\Tests\Persistence\Db\Mock\MockConnection;
use Dms\Core\Tests\Persistence\Db\Mock\MockDatabase;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class ReadModelRepositoryTest extends DbIntegrationTest
{
    /**
     * @var ReadModelRepository
     */
    protected $repo;

    /**
     * @var ReadModelMapper
     */
    protected $mapper;

    public function setUp()
    {
        $this->db         = new MockDatabase();
        $this->connection = new MockConnection($this->db);
        $this->repo       = $this->loadRepository($this->connection);
        $this->orm        = $this->repo->getMapper()->getDefinition()->getOrm();
        $this->mapper     = $this->repo->getParentMapper();
        $this->buildDatabase($this->db, $this->orm);
        $this->table = $this->db->getTable($this->mapper->getPrimaryTableName());
    }

    /**
     * @inheritDoc
     */
    final protected function loadOrm()
    {

    }

    /**
     * @param IConnection $connection
     *
     * @return ReadModelRepository
     */
    abstract protected function loadRepository(IConnection $connection);
}
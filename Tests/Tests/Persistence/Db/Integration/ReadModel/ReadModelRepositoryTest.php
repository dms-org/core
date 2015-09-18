<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\ReadModel;

use Iddigital\Cms\Core\Persistence\Db\Connection\IConnection;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IEntityMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\ReadModel\ReadModelMapper;
use Iddigital\Cms\Core\Persistence\ReadModelRepository;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\DbIntegrationTest;
use Iddigital\Cms\Core\Tests\Persistence\Db\Mock\MockConnection;
use Iddigital\Cms\Core\Tests\Persistence\Db\Mock\MockDatabase;

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
        $this->mapper     = $this->repo->getParentMapper();
        $this->buildDatabase($this->db, $this->mapper);
        $this->table = $this->db->getTable($this->mapper->getPrimaryTable()->getName());
    }

    /**
     * @return IEntityMapper
     */
    final protected function loadMapper()
    {

    }

    /**
     * @param IConnection $connection
     *
     * @return ReadModelRepository
     */
    abstract protected function loadRepository(IConnection $connection);
}
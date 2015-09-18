<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\ReadModel\Relation\ToOne;

use Iddigital\Cms\Core\Persistence\Db\Connection\IConnection;
use Iddigital\Cms\Core\Persistence\ReadModelRepository;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\ReadModel\Fixtures\LoadToOneIdRelation\ReadModelWithSubEntityReadModel;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\ReadModel\Fixtures\LoadToOneIdRelation\ReadModelWithSubEntityReadModelRepository;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\ReadModel\Fixtures\LoadToOneIdRelation\SubEntityReadModel;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\ReadModel\ReadModelRepositoryTest;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ReadModelWithSubEntityReadModelTest extends ReadModelRepositoryTest
{
    /**
     * @param IConnection $connection
     *
     * @return ReadModelRepository
     */
    protected function loadRepository(IConnection $connection)
    {
        return new ReadModelWithSubEntityReadModelRepository($connection);
    }

    public function testLoad()
    {
        $this->db->setData([
                'parent_entities' => [
                        ['id' => 1],
                        ['id' => 2],
                        ['id' => 3],
                ],
                'sub_entities'    => [
                        ['id' => 10, 'parent_id' => 1, 'val' => 100],
                        ['id' => 11, 'parent_id' => 2, 'val' => 200],
                        ['id' => 12, 'parent_id' => 3, 'val' => 300],
                ]
        ]);

        $this->assertEquals([
                new ReadModelWithSubEntityReadModel(new SubEntityReadModel(10, 100)),
                new ReadModelWithSubEntityReadModel(new SubEntityReadModel(11, 200)),
                new ReadModelWithSubEntityReadModel(new SubEntityReadModel(12, 300)),
        ], $this->repo->getAll());
    }
}
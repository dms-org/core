<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel\Relation\ToOne;

use Iddigital\Cms\Core\Persistence\Db\Connection\IConnection;
use Iddigital\Cms\Core\Persistence\ReadModelRepository;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToOneIdRelation\SubEntity;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel\Fixtures\LoadToOneIdRelation\ReadModelWithSubEntity;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel\Fixtures\LoadToOneIdRelation\ReadModelWithSubEntityRepository;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel\ReadModelRepositoryTest;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ReadModelWithSubEntityTest extends ReadModelRepositoryTest
{
    /**
     * @param IConnection $connection
     *
     * @return ReadModelRepository
     */
    protected function loadRepository(IConnection $connection)
    {
        return new ReadModelWithSubEntityRepository($connection);
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
                new ReadModelWithSubEntity(new SubEntity(100, 10)),
                new ReadModelWithSubEntity(new SubEntity(200, 11)),
                new ReadModelWithSubEntity(new SubEntity(300, 12)),
        ], $this->repo->getAll());
    }
}
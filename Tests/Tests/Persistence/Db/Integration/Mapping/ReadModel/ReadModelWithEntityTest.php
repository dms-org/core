<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel\Relation\ToOne;

use Iddigital\Cms\Core\Persistence\Db\Connection\IConnection;
use Iddigital\Cms\Core\Persistence\ReadModelRepository;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToOneIdRelation\ParentEntity;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToOneIdRelation\SubEntity;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel\Fixtures\WithEntity\ReadModelWithEntity;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel\Fixtures\WithEntity\ReadModelWithEntityRepository;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel\ReadModelRepositoryTest;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ReadModelWithEntityTest extends ReadModelRepositoryTest
{
    /**
     * @param IConnection $connection
     *
     * @return ReadModelRepository
     */
    protected function loadRepository(IConnection $connection)
    {
        return new ReadModelWithEntityRepository($connection);
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
                new ReadModelWithEntity(new ParentEntity(1, 10), new SubEntity(100, 10)),
                new ReadModelWithEntity(new ParentEntity(2, 11), new SubEntity(200, 11)),
                new ReadModelWithEntity(new ParentEntity(3, 12), new SubEntity(300, 12)),
        ], $this->repo->getAll());
    }
}
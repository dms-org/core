<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel;

use Dms\Core\Persistence\Db\Connection\IConnection;
use Dms\Core\Persistence\ReadModelRepository;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToOneRelation\SubEntity;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel\Fixtures\ToOneRelation\ReadModelWithToOneRelation;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel\Fixtures\ToOneRelation\ReadModelWithToOneRelationRepository;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ReadModelWithToOneRelationTest extends ReadModelRepositoryTest
{
    /**
     * @param IConnection $connection
     *
     * @return ReadModelRepository
     */
    protected function loadRepository(IConnection $connection)
    {
        return new ReadModelWithToOneRelationRepository($connection);
    }

    public function testLoad()
    {
        $this->setDataInDb([
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
                new ReadModelWithToOneRelation(new SubEntity(100, 10)),
                new ReadModelWithToOneRelation(new SubEntity(200, 11)),
                new ReadModelWithToOneRelation(new SubEntity(300, 12)),
        ], $this->repo->getAll());
    }

    public function testLoadPartial()
    {
        $this->setDataInDb([
                'parent_entities' => [
                        ['id' => 1],
                ],
                'sub_entities'    => [
                        ['id' => 10, 'parent_id' => 1, 'val' => 100],
                ]
        ]);

        $this->assertEquals(
                [
                        [
                                'subEntity'          => new SubEntity(100, 10),
                                'subEntity.val'      => 100,
                        ],
                ],
                $this->repo->loadMatching(
                        $this->repo->loadCriteria()
                                ->loadAll(['subEntity', 'subEntity.val'])
                ));
    }
}
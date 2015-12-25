<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel\Relation\ToOne;

use Dms\Core\Persistence\Db\Connection\IConnection;
use Dms\Core\Persistence\ReadModelRepository;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel\Fixtures\WithEntity\ReadModelWithEntity;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel\Fixtures\WithEntity\ReadModelWithEntityRepository;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel\ReadModelRepositoryTest;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToOneIdRelation\ParentEntity;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToOneIdRelation\SubEntity;

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
                new ReadModelWithEntity(new ParentEntity(1, 10), new SubEntity(100, 10)),
                new ReadModelWithEntity(new ParentEntity(2, 11), new SubEntity(200, 11)),
                new ReadModelWithEntity(new ParentEntity(3, 12), new SubEntity(300, 12)),
        ], $this->repo->getAll());
    }

    public function testLoadPartial()
    {
        $this->setDataInDb([
                'parent_entities' => [
                        ['id' => 1],
                        ['id' => 2],
                ],
                'sub_entities'    => [
                        ['id' => 10, 'parent_id' => 1, 'val' => 100],
                        ['id' => 11, 'parent_id' => 2, 'val' => 200],
                ]
        ]);

        $this->assertEquals(
                [
                        [
                                'parent'         => new ParentEntity(1, 10),
                                'parent.childId' => 10,
                                'child'          => new SubEntity(100, 10),
                                'child.val'      => 100,
                        ],
                        [
                                'parent'         => new ParentEntity(2, 11),
                                'parent.childId' => 11,
                                'child'          => new SubEntity(200, 11),
                                'child.val'      => 200,
                        ],
                ],
                $this->repo->loadMatching(
                        $this->repo->loadCriteria()
                                ->loadAll(['parent', 'parent.childId', 'child', 'child.val'])
                ));
    }
}
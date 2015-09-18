<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\ReadModel\Relation\ToOne;

use Iddigital\Cms\Core\Persistence\Db\Connection\IConnection;
use Iddigital\Cms\Core\Persistence\ReadModelRepository;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\ToManyIdRelation\ChildEntity;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\ReadModel\Fixtures\LoadToManyIdRelation\ReadModelWithChildEntities;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\ReadModel\Fixtures\LoadToManyIdRelation\ReadModelWithChildEntitiesRepository;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\ReadModel\ReadModelRepositoryTest;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ReadModelWithChildEntitiesTest extends ReadModelRepositoryTest
{
    /**
     * @param IConnection $connection
     *
     * @return ReadModelRepository
     */
    protected function loadRepository(IConnection $connection)
    {
        return new ReadModelWithChildEntitiesRepository($connection);
    }

    public function testLoad()
    {
        $this->db->setData([
                'parent_entities' => [
                        ['id' => 1],
                        ['id' => 2],
                        ['id' => 3],
                ],
                'child_entities'  => [
                        ['id' => 1, 'parent_id' => 1, 'val' => 10],
                        ['id' => 2, 'parent_id' => 1, 'val' => 20],
                        ['id' => 3, 'parent_id' => 1, 'val' => 30],
                        //
                        ['id' => 4, 'parent_id' => 2, 'val' => 10],
                        ['id' => 5, 'parent_id' => 2, 'val' => 20],
                        ['id' => 6, 'parent_id' => 2, 'val' => 30],
                        //
                        ['id' => 7, 'parent_id' => 3, 'val' => 10],
                        ['id' => 8, 'parent_id' => 3, 'val' => 20],
                        ['id' => 9, 'parent_id' => 3, 'val' => 30],
                ]
        ]);

        $this->assertEquals([
                new ReadModelWithChildEntities([
                        new ChildEntity(1, 10),
                        new ChildEntity(2, 20),
                        new ChildEntity(3, 30),
                ]),
                new ReadModelWithChildEntities([
                        new ChildEntity(4, 10),
                        new ChildEntity(5, 20),
                        new ChildEntity(6, 30),
                ]),
                new ReadModelWithChildEntities([
                        new ChildEntity(7, 10),
                        new ChildEntity(8, 20),
                        new ChildEntity(9, 30),
                ]),
        ], $this->repo->getAll());
    }
}
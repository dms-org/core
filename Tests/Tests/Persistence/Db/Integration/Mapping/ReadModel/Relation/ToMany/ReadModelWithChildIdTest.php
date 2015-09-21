<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel\Relation\ToOne;

use Iddigital\Cms\Core\Persistence\Db\Connection\IConnection;
use Iddigital\Cms\Core\Persistence\ReadModelRepository;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToManyIdRelation\ChildEntity;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel\Fixtures\LoadToManyIdRelation\ReadModelWithChildEntities;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel\Fixtures\LoadToManyIdRelation\ReadModelWithChildEntitiesRepository;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel\Fixtures\LoadToManyIdRelation\ReadModelWithChildIds;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel\Fixtures\LoadToManyIdRelation\ReadModelWithChildIdsRepository;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel\ReadModelRepositoryTest;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ReadModelWithChildIdTest extends ReadModelRepositoryTest
{
    /**
     * @param IConnection $connection
     *
     * @return ReadModelRepository
     */
    protected function loadRepository(IConnection $connection)
    {
        return new ReadModelWithChildIdsRepository($connection);
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
                new ReadModelWithChildIds([1, 2, 3]),
                new ReadModelWithChildIds([4, 5, 6]),
                new ReadModelWithChildIds([7, 8, 9]),
        ], $this->repo->getAll());
    }
}
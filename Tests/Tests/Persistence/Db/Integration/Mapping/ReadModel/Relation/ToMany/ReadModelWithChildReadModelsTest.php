<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel\Relation\ToOne;

use Iddigital\Cms\Core\Persistence\Db\Connection\IConnection;
use Iddigital\Cms\Core\Persistence\ReadModelRepository;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel\Fixtures\LoadToManyIdRelation\ChildEntityReadModel;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel\Fixtures\LoadToManyIdRelation\ReadModelWithChildReadModels;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel\Fixtures\LoadToManyIdRelation\ReadModelWithChildReadModelsRepository;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel\ReadModelRepositoryTest;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ReadModelWithChildReadModelsTest extends ReadModelRepositoryTest
{
    /**
     * @param IConnection $connection
     *
     * @return ReadModelRepository
     */
    protected function loadRepository(IConnection $connection)
    {
        return new ReadModelWithChildReadModelsRepository($connection);
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
                new ReadModelWithChildReadModels([
                        new ChildEntityReadModel(1, 10),
                        new ChildEntityReadModel(2, 20),
                        new ChildEntityReadModel(3, 30),
                ]),
                new ReadModelWithChildReadModels([
                        new ChildEntityReadModel(4, 10),
                        new ChildEntityReadModel(5, 20),
                        new ChildEntityReadModel(6, 30),
                ]),
                new ReadModelWithChildReadModels([
                        new ChildEntityReadModel(7, 10),
                        new ChildEntityReadModel(8, 20),
                        new ChildEntityReadModel(9, 30),
                ]),
        ], $this->repo->getAll());
    }
}
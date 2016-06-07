<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\ToManyId;

use Dms\Core\Persistence\Db\Mapping\IOrm;
use Dms\Core\Persistence\Db\Mapping\Relation\Lazy\Collection\LazyEntityCollection;
use Dms\Core\Persistence\Db\Mapping\Relation\Lazy\Collection\LazyEntityIdCollection;
use Dms\Core\Persistence\Db\Query\Clause\Join;
use Dms\Core\Persistence\Db\Query\Delete;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Persistence\Db\Query\Select;
use Dms\Core\Persistence\Db\Query\Upsert;
use Dms\Core\Persistence\Db\Schema\ForeignKeyMode;
use Dms\Core\Persistence\Db\Schema\Table;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\DbIntegrationTest;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToManyIdRelation\ParentEntity;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToManyIdRelation\ChildEntity;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToManyIdRelation\ParentEntityMapper;
use Dms\Core\Tests\Persistence\Db\Mock\MockDatabase;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class LazyToManyIdRelationTest extends DbIntegrationTest
{
    /**
     * @var Table
     */
    protected $parentEntities;

    /**
     * @var Table
     */
    protected $childEntities;

    /**
     * {@inheritDoc}
     */
    protected function buildDatabase(MockDatabase $db, IOrm $orm)
    {
        parent::buildDatabase($db, $orm);
        $this->parentEntities = $db->getTable('parent_entities')->getStructure();
        $this->childEntities  = $db->getTable('child_entities')->getStructure();
    }

    /**
     * @inheritDoc
     */
    protected function loadOrm()
    {
        $orm = ParentEntityMapper::orm();

        $orm->enableLazyLoading();

        return $orm;
    }

    public function testBulkLoad()
    {
        $this->setDataInDb([
                'parent_entities' => [
                        ['id' => 1],
                        ['id' => 2],
                        ['id' => 3],
                ],
                'child_entities'  => [
                        ['id' => 1, 'parent_id' => 1, 'val' => 10],
                        ['id' => 2, 'parent_id' => 1, 'val' => 20],
                        ['id' => 3, 'parent_id' => 1, 'val' => 30],

                        ['id' => 4, 'parent_id' => 2, 'val' => 10],
                        ['id' => 5, 'parent_id' => 2, 'val' => 20],
                        ['id' => 6, 'parent_id' => 2, 'val' => 30],

                        ['id' => 7, 'parent_id' => 3, 'val' => 10],
                        ['id' => 8, 'parent_id' => 3, 'val' => 20],
                        ['id' => 9, 'parent_id' => 3, 'val' => 30],
                ],
        ]);

        /** @var ParentEntity[] $parentEntities */
        $parentEntities = $this->repo->getAll();

        foreach ($parentEntities as $parentEntity) {
            /** @var LazyEntityIdCollection $children */
            $children = $parentEntity->childIds;

            $this->assertInstanceOf(LazyEntityIdCollection::class, $children);
            $this->assertSame(false, $children->hasLoadedElements());
        }

        $this->assertExecutedQueryTypes([
                'Load parent entities' => Select::class
        ]);

        // Child entities should be lazy loaded but only the first
        // access should perform a query. Then all children for all parents will be loaded.

        $firstChildren  = $parentEntities[0]->childIds->asArray();
        $secondChildren = $parentEntities[1]->childIds->asArray();
        $thirdChildren  = $parentEntities[2]->childIds->asArray();

        $this->assertExecutedQueryTypes([
                'Load parent entities' => Select::class,
                'Load *all* child ids' => Select::class,
        ]);

        $this->assertEquals([1, 2, 3], $firstChildren);
        $this->assertEquals([4, 5, 6], $secondChildren);
        $this->assertEquals([7, 8, 9], $thirdChildren);
    }

    public function testBulkPersistWithLazyCollectionsDoesNotPerformSyncQueries()
    {
        $this->setDataInDb([
                'parent_entities' => [
                        ['id' => 1],
                        ['id' => 2],
                        ['id' => 3],
                ],
                'child_entities'  => [
                        ['id' => 1, 'parent_id' => 1, 'val' => 10],
                        ['id' => 2, 'parent_id' => 1, 'val' => 20],
                        ['id' => 3, 'parent_id' => 1, 'val' => 30],

                        ['id' => 4, 'parent_id' => 2, 'val' => 10],
                        ['id' => 5, 'parent_id' => 2, 'val' => 20],
                        ['id' => 6, 'parent_id' => 2, 'val' => 30],

                        ['id' => 7, 'parent_id' => 3, 'val' => 10],
                        ['id' => 8, 'parent_id' => 3, 'val' => 20],
                        ['id' => 9, 'parent_id' => 3, 'val' => 30],
                ],
        ]);

        /** @var ParentEntity[] $parentEntities */
        $parentEntities = $this->repo->getAll();

        $this->repo->saveAll($parentEntities);

        $this->assertExecutedQueryTypes([
                'Load parent entities' => Select::class,
                'Save parent entities' => Upsert::class,
        ]);
    }
}
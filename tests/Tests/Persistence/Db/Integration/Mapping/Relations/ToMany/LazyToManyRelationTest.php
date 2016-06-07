<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\ToMany;

use Dms\Core\Persistence\Db\Mapping\IOrm;
use Dms\Core\Persistence\Db\Mapping\Relation\Lazy\Collection\LazyEntityCollection;
use Dms\Core\Persistence\Db\Query\Clause\Join;
use Dms\Core\Persistence\Db\Query\Delete;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Persistence\Db\Query\Select;
use Dms\Core\Persistence\Db\Query\Upsert;
use Dms\Core\Persistence\Db\Schema\ForeignKeyMode;
use Dms\Core\Persistence\Db\Schema\Table;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\DbIntegrationTest;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToManyRelation\ParentEntity;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToManyRelation\ChildEntity;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToManyRelation\IdentifyingParentEntityMapper;
use Dms\Core\Tests\Persistence\Db\Mock\MockDatabase;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class LazyToManyRelationTest extends DbIntegrationTest
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
        $orm = IdentifyingParentEntityMapper::orm();

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
            /** @var LazyEntityCollection $children */
            $children = $parentEntity->children;

            $this->assertInstanceOf(LazyEntityCollection::class, $children);
            $this->assertSame(false, $children->hasLoadedElements());
        }

        $this->assertExecutedQueryTypes([
                'Load parent entities' => Select::class
        ]);

        // Child entities should be lazy loaded but only the first
        // access should perform a query. Then all children for all parents will be loaded.

        $firstChildren  = $parentEntities[0]->children->asArray();
        $secondChildren = $parentEntities[1]->children->asArray();
        $thirdChildren  = $parentEntities[2]->children->asArray();

        $this->assertExecutedQueryTypes([
                'Load parent entities'      => Select::class,
                'Load *all* child entities' => Select::class,
        ]);

        $this->assertEquals([
                new ChildEntity(1, 10),
                new ChildEntity(2, 20),
                new ChildEntity(3, 30),
        ], $firstChildren);

        $this->assertEquals([
                new ChildEntity(4, 10),
                new ChildEntity(5, 20),
                new ChildEntity(6, 30),
        ], $secondChildren);

        $this->assertEquals([
                new ChildEntity(7, 10),
                new ChildEntity(8, 20),
                new ChildEntity(9, 30),
        ], $thirdChildren);
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
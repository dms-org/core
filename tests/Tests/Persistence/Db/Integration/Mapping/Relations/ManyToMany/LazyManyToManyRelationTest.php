<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\ManyToMany;

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
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ManyToManyRelation\AnotherEntity;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ManyToManyRelation\OneEntity;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ManyToManyRelation\OneEntityMapper;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToManyRelation\ChildEntity;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToManyRelation\IdentifyingParentEntityMapper;
use Dms\Core\Tests\Persistence\Db\Mock\MockDatabase;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class LazyManyToManyRelationTest extends DbIntegrationTest
{
    /**
     * @var Table
     */
    protected $oneTable;

    /**
     * @var Table
     */
    protected $joinTable;

    /**
     * @var Table
     */
    protected $anotherTable;

    /**
     * @inheritDoc
     */
    protected function loadOrm()
    {
        $orm = OneEntityMapper::orm();

        $orm->enableLazyLoading();

        return $orm;
    }

    /**
     * {@inheritDoc}
     */
    protected function buildDatabase(MockDatabase $db, IOrm $orm)
    {
        parent::buildDatabase($db, $orm);
        $this->oneTable     = $db->getTable('ones')->getStructure();
        $this->joinTable    = $db->getTable('one_anothers')->getStructure();
        $this->anotherTable = $db->getTable('anothers')->getStructure();
    }

    /**
     * @return void
     */
    public function testLoadWithSharedChildren()
    {
        $this->setDataInDb([
                'ones'         => [
                        ['id' => 1],
                        ['id' => 2],
                        ['id' => 3],
                ],
                'anothers'     => [
                        ['id' => 1, 'val' => 1],
                        ['id' => 2, 'val' => 2],
                        ['id' => 3, 'val' => 3],
                ],
                'one_anothers' => [
                        ['id' => 1, 'one_id' => 1, 'another_id' => 1],
                        ['id' => 2, 'one_id' => 1, 'another_id' => 2],
                        ['id' => 3, 'one_id' => 1, 'another_id' => 3],
                        ['id' => 4, 'one_id' => 2, 'another_id' => 1],
                        ['id' => 5, 'one_id' => 2, 'another_id' => 3],
                        ['id' => 6, 'one_id' => 3, 'another_id' => 2],
                        ['id' => 7, 'one_id' => 3, 'another_id' => 3],
                ],
        ]);

        $another1 = new AnotherEntity(1, 1);
        $another2 = new AnotherEntity(2, 2);
        $another3 = new AnotherEntity(3, 3);

        /** @var OneEntity[] $entities */
        $entities = [
                new OneEntity(1, [
                        $another1,
                        $another2,
                        $another3,
                ]),
                new OneEntity(2, [
                        $another1,
                        $another3,
                ]),
                new OneEntity(3, [
                        $another2,
                        $another3,
                ]),
        ];

        /** @var OneEntity[] $actual */
        $actual = $this->repo->getAll();

        $this->assertExecutedQueryTypes([
                'Load parent entities' => Select::class,
        ]);

        $this->assertInstanceOf(LazyEntityCollection::class, $actual[0]->others);

        $firstRelated  = $actual[0]->others->asArray();
        $secondRelated = $actual[1]->others->asArray();
        $thirdRelated  = $actual[2]->others->asArray();

        $this->assertExecutedQueryTypes([
                'Load parent entities' => Select::class,
                'Load child entities'  => Select::class,
        ]);

        $this->assertEquals($entities[0]->others->asArray(), $firstRelated);
        $this->assertEquals($entities[1]->others->asArray(), $secondRelated);
        $this->assertEquals($entities[2]->others->asArray(), $thirdRelated);
    }

    /**
     * @return void
     */
    public function testPersistWithLazyCollectionsDoesNotPerformSyncQueries()
    {
        $this->setDataInDb([
                'ones'         => [
                        ['id' => 1],
                        ['id' => 2],
                        ['id' => 3],
                ],
                'anothers'     => [
                        ['id' => 1, 'val' => 1],
                        ['id' => 2, 'val' => 2],
                        ['id' => 3, 'val' => 3],
                ],
                'one_anothers' => [
                        ['id' => 1, 'one_id' => 1, 'another_id' => 1],
                        ['id' => 2, 'one_id' => 1, 'another_id' => 2],
                        ['id' => 3, 'one_id' => 1, 'another_id' => 3],
                        ['id' => 4, 'one_id' => 2, 'another_id' => 1],
                        ['id' => 5, 'one_id' => 2, 'another_id' => 3],
                        ['id' => 6, 'one_id' => 3, 'another_id' => 2],
                        ['id' => 7, 'one_id' => 3, 'another_id' => 3],
                ],
        ]);

        /** @var OneEntity[] $loadedEntities */
        $loadedEntities = $this->repo->getAll();

        $this->assertExecutedQueryTypes([
                'Load parent entities' => Select::class,
        ]);

        $this->repo->saveAll($loadedEntities);

        $this->assertExecutedQueryTypes([
                'Load parent entities' => Select::class,
                'Save parent entities' => Upsert::class,
        ]);

        $this->connection->clearQueryLog();

        $loadedEntities[0]->others->asArray();
        $this->repo->saveAll($loadedEntities);

        $this->assertExecutedQueryTypes([
                'Load related entities'          => Select::class,
                'Save parent entities'           => Upsert::class,
                'Save related entities'          => Upsert::class,
                'Remove deleted join table rows' => Delete::class,
                'Save new join table rows'       => Upsert::class,
        ]);
    }
}
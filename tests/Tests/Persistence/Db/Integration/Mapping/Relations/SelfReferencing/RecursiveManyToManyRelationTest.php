<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\ManyToMany;

use Dms\Core\Persistence\Db\Mapping\IOrm;
use Dms\Core\Persistence\Db\Query\Clause\Join;
use Dms\Core\Persistence\Db\Query\Delete;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Persistence\Db\Query\Select;
use Dms\Core\Persistence\Db\Query\Upsert;
use Dms\Core\Persistence\Db\Schema\ForeignKey;
use Dms\Core\Persistence\Db\Schema\ForeignKeyMode;
use Dms\Core\Persistence\Db\Schema\Table;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\DbIntegrationTest;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\SelfReferencing\ManyToManyRelation\RecursiveEntity;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\SelfReferencing\ManyToManyRelation\RecursiveEntityMapper;
use Dms\Core\Tests\Persistence\Db\Mock\MockDatabase;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class RecursiveManyToManyRelationTest extends DbIntegrationTest
{
    /**
     * @var Table
     */
    protected $entities;

    /**
     * @var Table
     */
    protected $joinTable;

    /**
     * @inheritDoc
     */
    protected function loadOrm()
    {
        return RecursiveEntityMapper::orm();
    }

    /**
     * {@inheritDoc}
     */
    protected function buildDatabase(MockDatabase $db, IOrm $orm)
    {
        parent::buildDatabase($db, $orm);
        $this->entities  = $db->getTable('recursive_entities')->getStructure();
        $this->joinTable = $db->getTable('parents')->getStructure();
    }

    public function testCreatesForeignKeys()
    {
        $this->assertEquals(
                [
                        new ForeignKey(
                                'fk_parents_parent_id_recursive_entities',
                                ['parent_id'],
                                'recursive_entities',
                                ['id'],
                                ForeignKeyMode::CASCADE,
                                ForeignKeyMode::CASCADE
                        ),
                        new ForeignKey(
                                'fk_parents_child_id_recursive_entities',
                                ['child_id'],
                                'recursive_entities',
                                ['id'],
                                ForeignKeyMode::CASCADE,
                                ForeignKeyMode::CASCADE
                        ),
                ],
                array_values($this->joinTable->getForeignKeys())
        );
    }

    public function testPersistNoChildren()
    {
        $entity = new RecursiveEntity();

        $this->repo->save($entity);

        $this->assertDatabaseDataSameAs([
                'recursive_entities' => [
                        ['id' => 1]
                ],
                'parents'            => [],
        ]);

        $this->assertExecutedQueryTypes([
                'Insert entities' => Upsert::class,
        ]);
    }

    public function testPersistMultipleWithSharedChildren()
    {
        $another1 = new RecursiveEntity();
        $another2 = new RecursiveEntity();
        $another3 = new RecursiveEntity();
        /** @var RecursiveEntity[] $entities */
        $entities               = [
                new RecursiveEntity(null, [
                        $another1,
                        $another2,
                        $another3,
                ]),
                new RecursiveEntity(null, [
                        $another1,
                        $another3,
                ]),
                new RecursiveEntity(null, [
                        $another2,
                        $another3,
                ]),
        ];
        $entities[0]->parents[] = $entities[0];

        $this->repo->saveAll($entities);

        $this->assertDatabaseDataSameAs([
                'recursive_entities' => [
                        ['id' => 1],
                        ['id' => 2],
                        ['id' => 3],
                        ['id' => 4],
                        ['id' => 5],
                        ['id' => 6],
                ],
                'parents'            => [
                        ['id' => 1, 'parent_id' => 1, 'child_id' => 4],
                        ['id' => 2, 'parent_id' => 1, 'child_id' => 5],
                        ['id' => 3, 'parent_id' => 1, 'child_id' => 6],
                        ['id' => 4, 'parent_id' => 1, 'child_id' => 1],
                        ['id' => 5, 'parent_id' => 2, 'child_id' => 4],
                        ['id' => 6, 'parent_id' => 2, 'child_id' => 6],
                        ['id' => 7, 'parent_id' => 3, 'child_id' => 5],
                        ['id' => 8, 'parent_id' => 3, 'child_id' => 6],
                ],
        ]);

        $this->assertExecutedQueryTypes([
                'Insert parent entities'  => Upsert::class,
                'Insert related entities' => Upsert::class,
                'Insert join rows'        => Upsert::class,
        ]);
    }

    /**
     * @return void
     */
    public function testLoadWithSharedChildren()
    {
        $this->setDataInDb([
                'recursive_entities' => [
                        ['id' => 1],
                        ['id' => 2],
                        ['id' => 3],
                        ['id' => 4],
                        ['id' => 5],
                        ['id' => 6],
                ],
                'parents'            => [
                        ['id' => 1, 'parent_id' => 1, 'child_id' => 4],
                        ['id' => 2, 'parent_id' => 1, 'child_id' => 5],
                        ['id' => 3, 'parent_id' => 1, 'child_id' => 6],
                        ['id' => 4, 'parent_id' => 1, 'child_id' => 1],
                        ['id' => 5, 'parent_id' => 2, 'child_id' => 4],
                        ['id' => 6, 'parent_id' => 2, 'child_id' => 6],
                        ['id' => 7, 'parent_id' => 3, 'child_id' => 5],
                        ['id' => 8, 'parent_id' => 3, 'child_id' => 6],
                ],
        ]);

        $another4 = new RecursiveEntity(4);
        $another5 = new RecursiveEntity(5);
        $another3 = new RecursiveEntity(6);
        /** @var RecursiveEntity[] $entities */
        $entities             = [
                new RecursiveEntity(1),
                new RecursiveEntity(2, [
                        $another4,
                        $another3,
                ]),
                new RecursiveEntity(3, [
                        $another5,
                        $another3,
                ]),
        ];

        $entities[0]->parents = RecursiveEntity::collection([
                $entities[0],
                $another4,
                $another5,
                $another3,
        ]);

        /** @var RecursiveEntity[] $actual */
        $actual = $this->repo->tryGetAll([1, 2, 3]);
        $this->assertEquals(($entities), ($actual));

        $this->assertExecutedQueryTypes([
                'Load parent entities'                           => Select::class,
                'Load related entities'                          => Select::class,
                'Load related related entities (should be none)' => Select::class,
        ]);

        $this->assertExecutedQueryNumber(2, Select::from($this->entities)
                ->addRawColumn('id')
                ->addColumn('parent_id', Expr::tableColumn($this->joinTable, 'parent_id'))
                ->join(Join::inner($this->joinTable, $this->joinTable->getName(), [
                        Expr::equal(Expr::tableColumn($this->joinTable, 'child_id'), Expr::tableColumn($this->entities, 'id'))
                ]))
                ->where(Expr::in(
                        Expr::tableColumn($this->joinTable, 'parent_id'),
                        Expr::tuple([Expr::idParam(1), Expr::idParam(2), Expr::idParam(3)])
                ))
        );
    }

    public function testRemove()
    {
        $this->setDataInDb([
                'recursive_entities' => [
                        ['id' => 1],
                        ['id' => 2],
                        ['id' => 3],
                        ['id' => 4],
                        ['id' => 5],
                        ['id' => 6],
                ],
                'parents'            => [
                        ['id' => 1, 'parent_id' => 1, 'child_id' => 4],
                        ['id' => 2, 'parent_id' => 1, 'child_id' => 5],
                        ['id' => 3, 'parent_id' => 1, 'child_id' => 6],
                        ['id' => 4, 'parent_id' => 1, 'child_id' => 1],
                        ['id' => 5, 'parent_id' => 2, 'child_id' => 4],
                        ['id' => 6, 'parent_id' => 2, 'child_id' => 6],
                        ['id' => 7, 'parent_id' => 3, 'child_id' => 5],
                        ['id' => 8, 'parent_id' => 3, 'child_id' => 6],
                ],
        ]);


        $this->repo->removeAllById([1, 3, 4]);

        $this->assertDatabaseDataSameAs([
                'recursive_entities' => [
                        ['id' => 2],
                        ['id' => 5],
                        ['id' => 6],
                ],
                'parents'            => [
                        ['id' => 6, 'parent_id' => 2, 'child_id' => 6],
                ],
        ]);

        $this->assertExecutedQueryNumber(1,
                Delete::from($this->joinTable)
                        ->join(Join::inner($this->entities, 'recursive_entities', [
                                Expr::or_(
                                        Expr::equal(Expr::tableColumn($this->entities, 'id'),
                                                Expr::tableColumn($this->joinTable, 'parent_id')),
                                        Expr::equal(Expr::tableColumn($this->entities, 'id'),
                                                Expr::tableColumn($this->joinTable, 'child_id'))
                                )
                        ]))
                        ->where(Expr::in(Expr::tableColumn($this->entities, 'id'),
                                Expr::tuple([Expr::idParam(1), Expr::idParam(3), Expr::idParam(4)])))
        );
    }

    public function testLoadCriteriaWithRecursiveFlatten()
    {
        $this->setDataInDb([
                'recursive_entities' => [
                        ['id' => 1],
                        ['id' => 2],
                ],
                'parents'            => [
                        ['id' => 1, 'parent_id' => 1, 'child_id' => 1],
                        ['id' => 2, 'parent_id' => 1, 'child_id' => 2],
                        ['id' => 3, 'parent_id' => 2, 'child_id' => 1],
                        ['id' => 4, 'parent_id' => 2, 'child_id' => 2],
                ],
        ]);

        $this->assertEquals(
                [
                        ['id' => 1, 'count' => pow(2, 4)],
                        ['id' => 2, 'count' => pow(2, 4)],
                ],
                $this->repo->loadMatching(
                        $this->repo->loadCriteria()
                                ->loadAll([
                                        'id',
                                        'parents.flatten(parents).flatten(parents).flatten(parents).count()' => 'count',
                                ])
                )
        );
    }
}
<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\ManyToMany;

use Iddigital\Cms\Core\Persistence\Db\Mapping\IEntityMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IOrm;
use Iddigital\Cms\Core\Persistence\Db\Query\Clause\Join;
use Iddigital\Cms\Core\Persistence\Db\Query\Delete;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\Expr;
use Iddigital\Cms\Core\Persistence\Db\Query\Select;
use Iddigital\Cms\Core\Persistence\Db\Query\Upsert;
use Iddigital\Cms\Core\Persistence\Db\Schema\ForeignKey;
use Iddigital\Cms\Core\Persistence\Db\Schema\ForeignKeyMode;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\DbIntegrationTest;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\SelfReferencing\ManyToManyRelation\RecursiveEntity;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\SelfReferencing\ManyToManyRelation\RecursiveEntityMapper;
use Iddigital\Cms\Core\Tests\Persistence\Db\Mock\MockDatabase;

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
                        ['parent_id' => 1, 'child_id' => 4],
                        ['parent_id' => 1, 'child_id' => 5],
                        ['parent_id' => 1, 'child_id' => 6],
                        ['parent_id' => 1, 'child_id' => 1],
                        ['parent_id' => 2, 'child_id' => 4],
                        ['parent_id' => 2, 'child_id' => 6],
                        ['parent_id' => 3, 'child_id' => 5],
                        ['parent_id' => 3, 'child_id' => 6],
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
        $this->db->setData([
                'recursive_entities' => [
                        ['id' => 1],
                        ['id' => 2],
                        ['id' => 3],
                        ['id' => 4],
                        ['id' => 5],
                        ['id' => 6],
                ],
                'parents'            => [
                        ['parent_id' => 1, 'child_id' => 4],
                        ['parent_id' => 1, 'child_id' => 5],
                        ['parent_id' => 1, 'child_id' => 6],
                        ['parent_id' => 1, 'child_id' => 1],
                        ['parent_id' => 2, 'child_id' => 4],
                        ['parent_id' => 2, 'child_id' => 6],
                        ['parent_id' => 3, 'child_id' => 5],
                        ['parent_id' => 3, 'child_id' => 6],
                ],
        ]);

        $another1               = new RecursiveEntity(4);
        $another2               = new RecursiveEntity(5);
        $another3               = new RecursiveEntity(6);
        $entities               = [
                new RecursiveEntity(1, [
                        $another1,
                        $another2,
                        $another3,
                ]),
                new RecursiveEntity(2, [
                        $another1,
                        $another3,
                ]),
                new RecursiveEntity(3, [
                        $another2,
                        $another3,
                ]),
        ];
        $entities[0]->parents[] = $entities[0];

        /** @var RecursiveEntity[] $actual */
        $actual = $this->repo->tryGetAll([1, 2, 3]);
        $this->assertCount(3, $actual);
        $this->assertSame(1, $actual[0]->getId());
        $this->assertSame(4, $actual[0]->parents[0]->getId());
        $this->assertSame([], $actual[0]->parents[0]->parents->getAll());
        $this->assertSame(5, $actual[0]->parents[1]->getId());
        $this->assertSame([], $actual[0]->parents[1]->parents->getAll());
        $this->assertSame(6, $actual[0]->parents[2]->getId());
        $this->assertSame([], $actual[0]->parents[2]->parents->getAll());
        $this->assertSame($actual[0]->parents[0], $actual[1]->parents[0]);
        $this->assertSame($actual[0]->parents[3], $actual[0]);

        $this->assertSame(2, $actual[1]->getId());
        $this->assertSame($actual[0]->parents[0], $actual[1]->parents[0]);
        $this->assertSame($actual[0]->parents[2], $actual[1]->parents[1]);

        $this->assertSame(3, $actual[2]->getId());
        $this->assertSame($actual[0]->parents[1], $actual[2]->parents[0]);
        $this->assertSame($actual[0]->parents[2], $actual[2]->parents[1]);

        $this->assertExecutedQueryTypes([
                'Load parent entities'                           => Select::class,
                'Load related entities'                          => Select::class,
                'Load related related entities (should be none)' => Select::class,
        ]);

        $this->assertExecutedQueryNumber(2, Select::from($this->entities)
                ->addRawColumn('id')
                ->addColumn('parent_id', Expr::tableColumn($this->joinTable, 'parent_id'))
                ->join(Join::right($this->joinTable, $this->joinTable->getName(), [
                        Expr::equal(Expr::tableColumn($this->joinTable, 'child_id'), Expr::tableColumn($this->entities, 'id'))
                ]))
                ->where(Expr::in(
                        Expr::tableColumn( $this->joinTable, 'parent_id'),
                        Expr::tuple([Expr::idParam(1), Expr::idParam(2), Expr::idParam(3)])
                ))
        );
    }

    public function testRemove()
    {
        $this->db->setData([
                'recursive_entities' => [
                        ['id' => 1],
                        ['id' => 2],
                        ['id' => 3],
                        ['id' => 4],
                        ['id' => 5],
                        ['id' => 6],
                ],
                'parents'            => [
                        ['parent_id' => 1, 'child_id' => 4],
                        ['parent_id' => 1, 'child_id' => 5],
                        ['parent_id' => 1, 'child_id' => 6],
                        ['parent_id' => 1, 'child_id' => 1],
                        ['parent_id' => 2, 'child_id' => 4],
                        ['parent_id' => 2, 'child_id' => 6],
                        ['parent_id' => 3, 'child_id' => 5],
                        ['parent_id' => 3, 'child_id' => 6],
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
                        ['parent_id' => 2, 'child_id' => 6],
                ],
        ]);

        $this->assertExecutedQueryNumber(1,
                Delete::from($this->joinTable)
                        ->join(Join::inner($this->entities, 'recursive_entities', [
                            Expr::or_(
                                    Expr::equal(Expr::tableColumn($this->entities, 'id'), Expr::tableColumn($this->joinTable, 'parent_id')),
                                    Expr::equal(Expr::tableColumn($this->entities, 'id'), Expr::tableColumn($this->joinTable, 'child_id'))
                            )
                        ]))
                        ->where(Expr::in(Expr::tableColumn($this->entities, 'id'), Expr::tuple([Expr::idParam(1), Expr::idParam(3), Expr::idParam(4)])))
        );
    }
}
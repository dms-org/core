<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\ToMany;

use Iddigital\Cms\Core\Persistence\Db\Query\Clause\Join;
use Iddigital\Cms\Core\Persistence\Db\Query\Delete;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\Expr;
use Iddigital\Cms\Core\Persistence\Db\Query\Upsert;
use Iddigital\Cms\Core\Persistence\Db\Schema\ForeignKeyMode;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToManyRelation\ChildEntity;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToManyRelation\IdentifyingParentEntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class IdentifyingToManyRelationTest extends ToManyRelationTestBase
{
    /**
     * @inheritDoc
     */
    protected function loadOrm()
    {
        return IdentifyingParentEntityMapper::orm();
    }

    /**
     * @inheritDoc
     */
    protected function deleteForeignKeyMode()
    {
        return ForeignKeyMode::CASCADE;
    }

    public function testPersistParentWithId()
    {
        $this->db->setData([
                'parent_entities' => [
                        ['id' => 4]
                ],
        ]);

        // If the entity had an id it may
        // have contained previous child entities
        // must perform a delete (identifying) in case.
        $entity = $this->buildTestEntity([1000]);
        $entity->setId(4);

        $this->repo->save($entity);

        $this->assertDatabaseDataSameAs([
                'parent_entities' => [
                        ['id' => 4]
                ],
                'child_entities'  => [
                        ['id' => 1, 'parent_id' => 4, 'val' => 1000]
                ]
        ]);

        $this->assertExecutedQueryTypes([
                'Insert parent entities'                           => Upsert::class,
                'Remove previous child entities if there were any' => Delete::class,
                'Insert child entities'                            => Upsert::class,
        ]);

        $this->assertExecutedQueryNumber(2,
                Delete::from($this->childEntities)
                        ->where(Expr::equal(
                                Expr::tableColumn($this->childEntities, 'parent_id'),
                                Expr::idParam(4)
                        ))
        );
    }

    public function testRemove()
    {
        // Removing a parent should remove all children with identifying relationships
        $this->db->setData([
                'parent_entities' => [
                        ['id' => 1],
                ],
                'child_entities'  => [
                        ['id' => 10, 'parent_id' => 1, 'val' => 100],
                        ['id' => 11, 'parent_id' => 1, 'val' => 200],
                        ['id' => 12, 'parent_id' => 1, 'val' => 300],
                ]
        ]);

        $this->repo->removeById(1);

        $this->assertDatabaseDataSameAs([
                'parent_entities' => [],
                'child_entities'  => []
        ]);

        $this->assertExecutedQueries([
                'Delete child entities'  => Delete::from($this->childEntities)
                        ->join(Join::inner($this->parentEntities, $this->parentEntities->getName(), [
                                Expr::equal(
                                        Expr::tableColumn($this->childEntities, 'parent_id'),
                                        Expr::primaryKey($this->parentEntities)
                                )
                        ]))
                        ->where(Expr::in(
                                Expr::primaryKey($this->parentEntities),
                                Expr::tuple([Expr::idParam(1)])
                        )),
                'Delete parent entities' => Delete::from($this->parentEntities)
                        ->where(Expr::in(
                                Expr::primaryKey($this->parentEntities),
                                Expr::tuple([Expr::idParam(1)])
                        ))
        ]);
    }

    public function testRemoveBulk()
    {
        // Removing a parent should remove all children with identifying relationships
        $this->db->setData([
                'parent_entities' => [
                        ['id' => 1],
                        ['id' => 2],
                        ['id' => 3],
                ],
                'child_entities'  => [
                        ['id' => 10, 'parent_id' => 1, 'val' => 100],
                        ['id' => 11, 'parent_id' => 2, 'val' => 200],
                        ['id' => 12, 'parent_id' => 3, 'val' => 300],
                ]
        ]);

        $this->repo->removeAllById([1, 3]);

        $this->assertDatabaseDataSameAs([
                'parent_entities' => [
                        ['id' => 2],
                ],
                'child_entities'  => [
                        ['id' => 11, 'parent_id' => 2, 'val' => 200],
                ]
        ]);

        $this->assertExecutedQueries([
            // Should delete children first due to foreign key
            'Delete child entities'  => Delete::from($this->childEntities)
                    ->join(Join::inner($this->parentEntities, $this->parentEntities->getName(), [
                            Expr::equal(
                                    Expr::tableColumn($this->childEntities, 'parent_id'),
                                    Expr::primaryKey($this->parentEntities)
                            )
                    ]))
                    ->where(Expr::in(
                            Expr::primaryKey($this->parentEntities),
                            Expr::tuple([Expr::idParam(1), Expr::idParam(3)])
                    )),
            'Delete parent entities' => Delete::from($this->parentEntities)
                    ->where(Expr::in(
                            Expr::primaryKey($this->parentEntities),
                            Expr::tuple([Expr::idParam(1), Expr::idParam(3)])
                    ))
        ]);
    }

    public function testLoadPartial()
    {
        $this->db->setData([
                'parent_entities' => [
                        ['id' => 1],
                ],
                'child_entities'  => [
                        ['id' => 10, 'parent_id' => 1, 'val' => 100],
                        ['id' => 11, 'parent_id' => 1, 'val' => 200],
                        ['id' => 12, 'parent_id' => 1, 'val' => 300],
                ]
        ]);

        $this->assertEquals(
                [
                        [
                                'parent_id' => 1,
                                'children'  => ChildEntity::collection([
                                        new ChildEntity(10, 100),
                                        new ChildEntity(11, 200),
                                        new ChildEntity(12, 300),
                                ])
                        ],
                ],
                $this->repo->loadPartial(
                        $this->repo->partialCriteria()
                                ->loadAll(['id' => 'parent_id',  'children'])
                ));
    }
}
<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\ToOne;

use Dms\Core\Persistence\Db\Query\Clause\Join;
use Dms\Core\Persistence\Db\Query\Delete;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Persistence\Db\Query\Upsert;
use Dms\Core\Persistence\Db\Schema\ForeignKeyMode;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToOneRelation\IdentifyingParentEntityMapper;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToOneRelation\SubEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class IdentifyingToOneRelationTest extends ToOneRelationTestBase
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
        $this->setDataInDb([
                'parent_entities' => [
                        ['id' => 4]
                ],
        ]);

        // If the entity had an id it may
        // have contained previous child entities
        // must perform a delete (identifying) in case.
        $entity = $this->buildTestEntity(4, 123);

        $this->repo->save($entity);

        $this->assertDatabaseDataSameAs([
                'parent_entities' => [
                        ['id' => 4]
                ],
                'sub_entities'    => [
                        ['id' => 1, 'parent_id' => 4, 'val' => 123]
                ]
        ]);

        $this->assertExecutedQueryTypes([
                'Insert parent entities'                           => Upsert::class,
                'Remove previous child entities if there were any' => Delete::class,
                'Insert child entities'                            => Upsert::class,
        ]);

        $this->assertExecutedQueryNumber(2,
                Delete::from($this->subEntities)
                        ->where(Expr::equal(
                                Expr::tableColumn($this->subEntities, 'parent_id'),
                                Expr::idParam(4)
                        ))
        );
    }

    public function testRemove()
    {
        // Removing a parent should remove all children with identifying relationships
        $this->setDataInDb([
                'parent_entities' => [
                        ['id' => 1],
                ],
                'sub_entities'    => [
                        ['id' => 10, 'parent_id' => 1, 'val' => 100],
                ]
        ]);

        $this->repo->removeById(1);

        $this->assertDatabaseDataSameAs([
                'parent_entities' => [],
                'sub_entities'    => []
        ]);

        $this->assertExecutedQueries([
                'Delete child entities'  => Delete::from($this->subEntities)
                        ->join(Join::inner($this->parentEntities, $this->parentEntities->getName(), [
                                Expr::equal(
                                        Expr::tableColumn($this->subEntities, 'parent_id'),
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

        $this->repo->removeAllById([1, 3]);

        $this->assertDatabaseDataSameAs([
                'parent_entities' => [
                        ['id' => 2],
                ],
                'sub_entities'    => [
                        ['id' => 11, 'parent_id' => 2, 'val' => 200],
                ]
        ]);

        $this->assertExecutedQueries([
            // Should delete children first due to foreign key
            'Delete child entities'  => Delete::from($this->subEntities)
                    ->join(Join::inner($this->parentEntities, $this->parentEntities->getName(), [
                            Expr::equal(
                                    Expr::tableColumn($this->subEntities, 'parent_id'),
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

    public function testLoadCriteria()
    {
        $this->setDataInDb([
                'parent_entities' => [
                        ['id' => 1],
                ],
                'sub_entities'    => [
                        ['id' => 10, 'parent_id' => 1, 'val' => 100],
                ]
        ]);

        $this->assertEquals(
                [
                        ['child' => new SubEntity(100, 10), 'child.val' => 100],
                ],
                $this->repo->loadMatching(
                        $this->repo->loadCriteria()
                                ->loadAll(['child', 'child.val'])
                ));
    }
}
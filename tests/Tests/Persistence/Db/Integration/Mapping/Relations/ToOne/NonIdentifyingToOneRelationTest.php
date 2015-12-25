<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\ToOne;

use Dms\Core\Persistence\Db\Mapping\IEntityMapper;
use Dms\Core\Persistence\Db\Query\Clause\Join;
use Dms\Core\Persistence\Db\Query\Delete;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Persistence\Db\Query\Update;
use Dms\Core\Persistence\Db\Query\Upsert;
use Dms\Core\Persistence\Db\Schema\ForeignKeyMode;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToOneRelation\NonIdentifyingParentEntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class NonIdentifyingToOneRelationTest extends ToOneRelationTestBase
{
    /**
     * @inheritDoc
     */
    protected function loadOrm()
    {
        return NonIdentifyingParentEntityMapper::orm();
    }

    /**
     * @inheritDoc
     */
    protected function deleteForeignKeyMode()
    {
        return ForeignKeyMode::SET_NULL;
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
        // must perform a update to disassociate previous
        // child foreign keys in case.
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
                'Insert parent entities'                                 => Upsert::class,
                'Disassociate previous child entities if there were any' => Update::class,
                'Insert child entities'                                  => Upsert::class,
        ]);

        $this->assertExecutedQueryNumber(2,
                Update::from($this->subEntities)
                        ->set('parent_id', Expr::idParam(null))
                        ->where(Expr::equal(
                                Expr::tableColumn($this->subEntities, 'parent_id'),
                                Expr::idParam(4)
                        ))
        );
    }

    public function testRemove()
    {
        // Removing a parent should set the foreign keys of the children to null
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
                'sub_entities'    => [
                        ['id' => 10, 'parent_id' => null, 'val' => 100],
                ]
        ]);

        $this->assertExecutedQueries([
                'Disassociate child entities' => Update::from($this->subEntities)
                        ->set('parent_id', Expr::idParam(null))
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
                'Delete parent entities'      => Delete::from($this->parentEntities)
                        ->where(Expr::in(
                                Expr::primaryKey($this->parentEntities),
                                Expr::tuple([Expr::idParam(1)])
                        ))
        ]);
    }

    public function testRemoveBulk()
    {
        // Removing a parent should set foreign keys to null with non-identifying relationships
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
                        ['id' => 10, 'parent_id' => null, 'val' => 100],
                        ['id' => 11, 'parent_id' => 2, 'val' => 200],
                        ['id' => 12, 'parent_id' => null, 'val' => 300],
                ]
        ]);

        $this->assertExecutedQueries([
            // Should dissociate children first due to foreign key
            'Delete child entities'  => Update::from($this->subEntities)
                    ->set('parent_id', Expr::idParam(null))
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
}
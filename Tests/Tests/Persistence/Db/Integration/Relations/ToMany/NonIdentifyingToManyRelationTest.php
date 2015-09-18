<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Relations\ToMany;

use Iddigital\Cms\Core\Persistence\Db\Mapping\IEntityMapper;
use Iddigital\Cms\Core\Persistence\Db\Query\Clause\Join;
use Iddigital\Cms\Core\Persistence\Db\Query\Delete;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\Expr;
use Iddigital\Cms\Core\Persistence\Db\Query\Update;
use Iddigital\Cms\Core\Persistence\Db\Query\Upsert;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\ToManyRelation\NonIdentifyingParentEntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class NonIdentifyingToManyRelationTest extends ToManyRelationTestBase
{
    /**
     * @return IEntityMapper
     */
    protected function loadMapper()
    {
        return new NonIdentifyingParentEntityMapper();
    }

    public function testPersistParentWithId()
    {
        // If the entity had an id it may
        // have contained previous child entities
        // must perform a update to disassociate previous
        // child foreign keys in case.
        $entity = $this->buildTestEntity([123]);
        $entity->setId(4);

        $this->repo->save($entity);

        $this->assertDatabaseDataSameAs([
                'parent_entities' => [
                        ['id' => 4]
                ],
                'child_entities'    => [
                        ['id' => 1, 'parent_id' => 4, 'val' => 123]
                ]
        ]);

        $this->assertExecutedQueryTypes([
                'Insert parent entities'                                 => Upsert::class,
                'Disassociate previous child entities if there were any' => Update::class,
                'Insert child entities'                                  => Upsert::class,
        ]);

        $this->assertExecutedQueryNumber(2,
                Update::from($this->childEntities)
                        ->set('parent_id', Expr::idParam(null))
                        ->where(Expr::equal(
                                Expr::tableColumn($this->childEntities, 'parent_id'),
                                Expr::idParam(4)
                        ))
        );
    }

    public function testRemove()
    {
        // Removing a parent should set the foreign keys of the children to null
        $this->db->setData([
                'parent_entities' => [
                        ['id' => 1],
                ],
                'child_entities'    => [
                        ['id' => 10, 'parent_id' => 1, 'val' => 100],
                ]
        ]);

        $this->repo->removeById(1);

        $this->assertDatabaseDataSameAs([
                'parent_entities' => [],
                'child_entities'    => [
                        ['id' => 10, 'parent_id' => null, 'val' => 100],
                ]
        ]);

        $this->assertExecutedQueries([
                'Disassociate child entities' => Update::from($this->childEntities)
                        ->set('parent_id', Expr::idParam(null))
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
                'Delete parent entities'      => Delete::from($this->parentEntities)
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
                'child_entities'    => [
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
                'child_entities'    => [
                        ['id' => 10, 'parent_id' => null, 'val' => 100],
                        ['id' => 11, 'parent_id' => 2, 'val' => 200],
                        ['id' => 12, 'parent_id' => null, 'val' => 300],
                ]
        ]);

        $this->assertExecutedQueries([
            // Should dissociate children first due to foreign key
            'Delete child entities'  => Update::from($this->childEntities)
                    ->set('parent_id', Expr::idParam(null))
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
}
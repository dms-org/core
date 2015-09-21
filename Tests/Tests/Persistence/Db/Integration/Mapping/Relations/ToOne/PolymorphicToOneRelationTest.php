<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\ToOne;

use Iddigital\Cms\Core\Persistence\Db\Mapping\IEntityMapper;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\DbIntegrationTest;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToOneRelation\ParentEntity;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToOneRelation\Polymorphic\ParentEntityMapper;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToOneRelation\Polymorphic\SubEntitySubclass;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToOneRelation\SubEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PolymorphicToOneRelationTest extends DbIntegrationTest
{
    /**
     * @inheritDoc
     */
    protected function loadOrm()
    {
        return ParentEntityMapper::orm();
    }

    public function testPersist()
    {
        $entities = [
                new ParentEntity(null, new SubEntity(100)),
                new ParentEntity(null, new SubEntitySubclass(200, 'sub')),
        ];

        $this->repo->saveAll($entities);

        $this->assertDatabaseDataSameAs([
                'parent_entities'         => [
                        ['id' => 1],
                        ['id' => 2],
                ],
                'sub_entities'            => [
                        ['id' => 1, 'parent_id' => 1, 'val' => 100],
                        ['id' => 2, 'parent_id' => 2, 'val' => 200],
                ],
                'sub_entities_subclasses' => [
                        ['id' => 2, 'sub' => 'sub'],
                ]
        ]);
    }

    public function testLoad()
    {

        $this->db->setData([
                'parent_entities'         => [
                        ['id' => 1],
                        ['id' => 2],
                ],
                'sub_entities'            => [
                        ['id' => 1, 'parent_id' => 1, 'val' => 100],
                        ['id' => 2, 'parent_id' => 2, 'val' => 200],
                ],
                'sub_entities_subclasses' => [
                        ['id' => 2, 'sub' => 'sub'],
                ]
        ]);

        $child = new SubEntity(100);
        $child->setId(1);
        $child1 = new SubEntitySubclass(200, 'sub');
        $child1->setId(2);
        $this->assertEquals([
                new ParentEntity(1, $child),
                new ParentEntity(2, $child1),
        ], $this->repo->getAll());
    }

    public function testRemoveBulk()
    {
        // Removing a parent should remove all children with identifying relationships
        $this->db->setData([
                'parent_entities'         => [
                        ['id' => 1],
                        ['id' => 2],
                ],
                'sub_entities'            => [
                        ['id' => 1, 'parent_id' => 1, 'val' => 100],
                        ['id' => 2, 'parent_id' => 2, 'val' => 200],
                ],
                'sub_entities_subclasses' => [
                        ['id' => 2, 'sub' => 'sub'],
                ]
        ]);

        $this->repo->removeAllById([1, 2]);

        $this->assertDatabaseDataSameAs([
                'parent_entities'         => [
                ],
                'sub_entities'            => [
                ],
                'sub_entities_subclasses' => [
                ]
        ]);
    }
}
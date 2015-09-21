<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\ToMany;

use Iddigital\Cms\Core\Persistence\Db\Mapping\IEntityMapper;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\DbIntegrationTest;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToManyRelation\ChildEntity;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToManyRelation\ParentEntity;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToManyRelation\Polymorphic\ChildEntitySubclass;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToManyRelation\Polymorphic\ParentEntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PolymorphicToManyRelationTest extends DbIntegrationTest
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
        $entity = new ParentEntity(null, [
                new ChildEntity(null, 1000),
                new ChildEntitySubclass(null, 500, 'subclass')
        ]);

        $this->repo->save($entity);

        $this->assertDatabaseDataSameAs([
                'parent_entities'  => [
                        ['id' => 1]
                ],
                'child_entities'   => [
                        ['id' => 1, 'parent_id' => 1, 'val' => 1000],
                        ['id' => 2, 'parent_id' => 1, 'val' => 500],
                ],
                'child_subclasses' => [
                        ['id' => 2, 'sub' => 'subclass']
                ]
        ]);
    }

    public function testLoad()
    {
        $this->db->setData([
                'parent_entities'  => [
                        ['id' => 1]
                ],
                'child_entities'   => [
                        ['id' => 1, 'parent_id' => 1, 'val' => 1000],
                        ['id' => 2, 'parent_id' => 1, 'val' => 500],
                ],
                'child_subclasses' => [
                        ['id' => 2, 'sub' => 'subclass']
                ]
        ]);

        $childEntity = new ChildEntity(1, 1000);
        $childEntitySubclass = new ChildEntitySubclass(2, 500, 'subclass');
        $this->assertEquals(new ParentEntity(1, [
                $childEntity,
                $childEntitySubclass
        ]), $this->repo->get(1));
    }

    public function testRemoveBulk()
    {
        $this->db->setData([
                'parent_entities'  => [
                        ['id' => 1],
                        ['id' => 2],
                        ['id' => 3],
                ],
                'child_entities'   => [
                        ['id' => 10, 'parent_id' => 1, 'val' => 100],
                        ['id' => 11, 'parent_id' => 2, 'val' => 200],
                        ['id' => 12, 'parent_id' => 3, 'val' => 300],
                ],
                'child_subclasses' => [
                        ['id' => 11, 'sub' => 'subclass1'],
                        ['id' => 12, 'sub' => 'subclass2'],
                ]
        ]);

        $this->repo->removeAllById([1, 3]);

        $this->assertDatabaseDataSameAs([
                'parent_entities'  => [
                        ['id' => 2],
                ],
                'child_entities'   => [
                        ['id' => 11, 'parent_id' => 2, 'val' => 200],
                ],
                'child_subclasses' => [
                        ['id' => 11, 'sub' => 'subclass1']
                ]
        ]);
    }
}
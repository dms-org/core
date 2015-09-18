<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Relations\ToMany;

use Iddigital\Cms\Core\Persistence\Db\Mapping\IEntityMapper;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\DbIntegrationTest;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\ToManyRelation\ChildEntity;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\ToManyRelation\ParentEntity;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\ToManyRelation\Polymorphic\ChildEntitySubclass;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\ToManyRelation\Polymorphic\ParentEntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PolymorphicToManyRelationTest extends DbIntegrationTest
{
    /**
     * @return IEntityMapper
     */
    protected function loadMapper()
    {
        return new ParentEntityMapper();
    }

    public function testPersist()
    {
        $entity = new ParentEntity(null, [
                new ChildEntity(1000),
                new ChildEntitySubclass(500, 'subclass')
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

        $childEntity = new ChildEntity(1000);
        $childEntity->setId(1);
        $childEntitySubclass = new ChildEntitySubclass(500, 'subclass');
        $childEntitySubclass->setId(2);
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
<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping;

use Dms\Core\Persistence\Db\Mapping\Relation\Lazy\Collection\LazyEntityCollection;
use Dms\Core\Persistence\Db\Mapping\Relation\Lazy\Collection\LazyValueObjectCollection;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObjectCollectionWithEntityRelation\ChildChildEntity;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObjectCollectionWithEntityRelation\ChildChildValueObject;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObjectCollectionWithEntityRelation\ChildEntity;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObjectCollectionWithEntityRelation\ChildValueObject;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObjectCollectionWithEntityRelation\ParentEntity;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObjectCollectionWithEntityRelation\ParentEntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ValueObjectCollectionWithEntityRelationTest extends DbIntegrationTest
{
    public function setUp(): void
    {
        parent::setUp();
    }

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
            new ChildValueObject(new ChildEntity(null, [
                new ChildChildEntity(),
                new ChildChildEntity(),
                new ChildChildEntity(),
            ]), [
                new ChildChildValueObject(),
                new ChildChildValueObject(),
                new ChildChildValueObject(),
            ]),
            new ChildValueObject(new ChildEntity(null, [
                new ChildChildEntity(),
                new ChildChildEntity(),
                new ChildChildEntity(),
            ]), [
                new ChildChildValueObject(),
                new ChildChildValueObject(),
                new ChildChildValueObject(),
            ]),
        ]);

        $this->repo->save($entity);

        $this->assertDatabaseDataSameAs([
            'parents'       => [
                ['id' => 1],
            ],
            'value_objects' => [
                ['id' => 1, 'parent_id' => 1, 'child_id' => 1],
                ['id' => 2, 'parent_id' => 1, 'child_id' => 2],
            ],
            'value_object_children' => [
                ['id' => 1, 'value_object_id' => 1, 'data' => ''],
                ['id' => 2, 'value_object_id' => 1, 'data' => ''],
                ['id' => 3, 'value_object_id' => 1, 'data' => ''],
                ['id' => 4, 'value_object_id' => 2, 'data' => ''],
                ['id' => 5, 'value_object_id' => 2, 'data' => ''],
                ['id' => 6, 'value_object_id' => 2, 'data' => ''],
            ],
            'children'      => [
                ['id' => 1],
                ['id' => 2],
            ],
            'children_children' => [
                ['id' => 1, 'data' => '', 'child_id' => 1],
                ['id' => 2, 'data' => '', 'child_id' => 1],
                ['id' => 3, 'data' => '', 'child_id' => 1],
                ['id' => 4, 'data' => '', 'child_id' => 2],
                ['id' => 5, 'data' => '', 'child_id' => 2],
                ['id' => 6, 'data' => '', 'child_id' => 2],
            ]
        ]);

        $this->assertEquals($entity, $this->repo->get(1));
    }

    public function testPersistExisting()
    {
        $this->setDataInDb([
            'parents'       => [
                ['id' => 1],
            ],
            'value_objects' => [
                ['id' => 1, 'parent_id' => 1, 'child_id' => 1],
                ['id' => 2, 'parent_id' => 1, 'child_id' => 2],
            ],
            'value_object_children' => [
                ['id' => 1, 'value_object_id' => 1, 'data' => ''],
                ['id' => 2, 'value_object_id' => 1, 'data' => ''],
                ['id' => 3, 'value_object_id' => 1, 'data' => ''],
                ['id' => 4, 'value_object_id' => 2, 'data' => ''],
                ['id' => 5, 'value_object_id' => 2, 'data' => ''],
                ['id' => 6, 'value_object_id' => 2, 'data' => ''],
            ],
            'children'      => [
                ['id' => 1],
                ['id' => 2],
            ],
            'children_children' => [
                ['id' => 1, 'data' => '', 'child_id' => 1],
                ['id' => 2, 'data' => '', 'child_id' => 1],
                ['id' => 3, 'data' => '', 'child_id' => 1],
                ['id' => 4, 'data' => '', 'child_id' => 2],
                ['id' => 5, 'data' => '', 'child_id' => 2],
                ['id' => 6, 'data' => '', 'child_id' => 2],
            ]
        ]);

        $this->repo->save($this->repo->get(1));

        $this->assertDatabaseDataSameAs([
            'parents'       => [
                ['id' => 1],
            ],
            'value_objects' => [
                ['id' => 3, 'parent_id' => 1, 'child_id' => 1],
                ['id' => 4, 'parent_id' => 1, 'child_id' => 2],
            ],
            'value_object_children' => [
                ['id' => 7, 'value_object_id' => 3, 'data' => ''],
                ['id' => 8, 'value_object_id' => 3, 'data' => ''],
                ['id' => 9, 'value_object_id' => 3, 'data' => ''],
                ['id' => 10, 'value_object_id' => 4, 'data' => ''],
                ['id' => 11, 'value_object_id' => 4, 'data' => ''],
                ['id' => 12, 'value_object_id' => 4, 'data' => ''],
            ],
            'children'      => [
                ['id' => 1],
                ['id' => 2],
            ],
            'children_children' => [
                ['id' => 1, 'data' => '', 'child_id' => 1],
                ['id' => 2, 'data' => '', 'child_id' => 1],
                ['id' => 3, 'data' => '', 'child_id' => 1],
                ['id' => 4, 'data' => '', 'child_id' => 2],
                ['id' => 5, 'data' => '', 'child_id' => 2],
                ['id' => 6, 'data' => '', 'child_id' => 2],
            ]
        ]);
    }

    public function testPersistExistingWithLazyLoadedCollectionDoesNotLoadEntityCollection()
    {
        $this->orm->enableLazyLoading();

        $this->setDataInDb([
            'parents'       => [
                ['id' => 1],
            ],
            'value_objects' => [
                ['id' => 1, 'parent_id' => 1, 'child_id' => 1],
                ['id' => 2, 'parent_id' => 1, 'child_id' => 2],
            ],
            'children'      => [
                ['id' => 1],
                ['id' => 2],
            ],
            'value_object_children' => [
                ['id' => 1, 'value_object_id' => 1, 'data' => ''],
                ['id' => 2, 'value_object_id' => 1, 'data' => ''],
                ['id' => 3, 'value_object_id' => 1, 'data' => ''],
                ['id' => 4, 'value_object_id' => 2, 'data' => ''],
                ['id' => 5, 'value_object_id' => 2, 'data' => ''],
                ['id' => 6, 'value_object_id' => 2, 'data' => ''],
            ],
            'children_children' => [
                ['id' => 1, 'data' => '', 'child_id' => 1],
                ['id' => 2, 'data' => '', 'child_id' => 1],
                ['id' => 3, 'data' => '', 'child_id' => 1],
                ['id' => 4, 'data' => '', 'child_id' => 2],
                ['id' => 5, 'data' => '', 'child_id' => 2],
                ['id' => 6, 'data' => '', 'child_id' => 2],
            ]
        ]);

        $entity = $this->repo->get(1);

        $this->assertInstanceOf(LazyValueObjectCollection::class, $entity->valueObjects);
        $entity->valueObjects->asArray(); // Force load

        $this->repo->save($entity);

        // Should not load nested entity collection
        $this->assertInstanceOf(LazyEntityCollection::class, $entity->valueObjects[0]->entity->children);
        $this->assertEquals(false, $entity->valueObjects[0]->entity->children->hasLoadedElements());

        // Should load nested value object collection
        $this->assertInstanceOf(LazyValueObjectCollection::class, $entity->valueObjects[0]->children);
//        $this->assertEquals(true, $entity->valueObjects[0]->children->hasLoadedElements());

        $this->assertDatabaseDataSameAs([
            'parents'       => [
                ['id' => 1],
            ],
            'value_objects' => [
                ['id' => 3, 'parent_id' => 1, 'child_id' => 1],
                ['id' => 4, 'parent_id' => 1, 'child_id' => 2],
            ],
            'value_object_children' => [
                ['id' => 7, 'value_object_id' => 3, 'data' => ''],
                ['id' => 8, 'value_object_id' => 3, 'data' => ''],
                ['id' => 9, 'value_object_id' => 3, 'data' => ''],
                ['id' => 10, 'value_object_id' => 4, 'data' => ''],
                ['id' => 11, 'value_object_id' => 4, 'data' => ''],
                ['id' => 12, 'value_object_id' => 4, 'data' => ''],
            ],
            'children'      => [
                ['id' => 1],
                ['id' => 2],
            ],
            'children_children' => [
                ['id' => 1, 'data' => '', 'child_id' => 1],
                ['id' => 2, 'data' => '', 'child_id' => 1],
                ['id' => 3, 'data' => '', 'child_id' => 1],
                ['id' => 4, 'data' => '', 'child_id' => 2],
                ['id' => 5, 'data' => '', 'child_id' => 2],
                ['id' => 6, 'data' => '', 'child_id' => 2],
            ]
        ]);
    }
}
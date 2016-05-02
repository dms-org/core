<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping;

use Dms\Core\Persistence\Db\Mapping\IOrm;
use Dms\Core\Persistence\Db\Schema\Column;
use Dms\Core\Persistence\Db\Schema\ForeignKey;
use Dms\Core\Persistence\Db\Schema\ForeignKeyMode;
use Dms\Core\Persistence\Db\Schema\PrimaryKeyBuilder;
use Dms\Core\Persistence\Db\Schema\Type\Integer;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObjectWithManyToManyRelation\ChildEntity;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObjectWithManyToManyRelation\EmbeddedObject;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObjectWithManyToManyRelation\EntityWithValueObject;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObjectWithManyToManyRelation\EntityWithValueObjectMapper;
use Dms\Core\Tests\Persistence\Db\Mock\MockDatabase;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ValueObjectWithManyToManyRelationTest extends DbIntegrationTest
{
    /**
     * @inheritDoc
     */
    protected function loadOrm()
    {
        return EntityWithValueObjectMapper::orm();
    }

    /**
     * @inheritDoc
     */
    protected function buildDatabase(MockDatabase $db, IOrm $orm)
    {
        parent::buildDatabase($db, $orm);
    }

    public function testCreatesForeignKey()
    {
        $this->assertEquals([
            new ForeignKey(
                'fk_parent_children_parent_id_entities',
                ['parent_id'],
                'entities',
                ['id'],
                ForeignKeyMode::CASCADE,
                ForeignKeyMode::CASCADE
            ),
            new ForeignKey(
                'fk_parent_children_child_id_children',
                ['child_id'],
                'children',
                ['id'],
                ForeignKeyMode::CASCADE,
                ForeignKeyMode::CASCADE
            ),
        ], array_values($this->db->getTable('parent_children')->getStructure()->getForeignKeys()));
    }

    public function testCorrectTableLayout()
    {
        $this->assertDatabaseStructureSameAs([
            'entities' => [
                PrimaryKeyBuilder::incrementingInt('id'),
            ],
            'parent_children' => [
                PrimaryKeyBuilder::incrementingInt('id'),
                new Column('parent_id', Integer::normal()->unsigned()),
                new Column('child_id', Integer::normal()->unsigned()),
            ],
            'children' => [
                PrimaryKeyBuilder::incrementingInt('id'),
            ],
        ]);
    }

    public function testPersist()
    {
        $entity = new EntityWithValueObject(null, new EmbeddedObject([
            new ChildEntity(),
            new ChildEntity(),
            new ChildEntity(),
        ]));

        $this->repo->save($entity);

        $this->assertDatabaseDataSameAs([
            'entities' => [
                ['id' => 1],
            ],
            'parent_children' => [
                ['id' => 1, 'parent_id' => 1, 'child_id' => 1],
                ['id' => 2, 'parent_id' => 1, 'child_id' => 2],
                ['id' => 3, 'parent_id' => 1, 'child_id' => 3],
            ],
            'children' => [
                ['id' => 1],
                ['id' => 2],
                ['id' => 3],
            ],
        ]);
    }

    public function testLoad()
    {
        $this->setDataInDb([
            'entities' => [
                ['id' => 1],
            ],
            'parent_children' => [
                ['id' => 1, 'parent_id' => 1, 'child_id' => 1],
                ['id' => 2, 'parent_id' => 1, 'child_id' => 2],
                ['id' => 3, 'parent_id' => 1, 'child_id' => 3],
            ],
            'children' => [
                ['id' => 1],
                ['id' => 2],
                ['id' => 3],
            ],
        ]);

        $actual = $this->repo->get(1);

        $this->assertEquals(new EntityWithValueObject(1, new EmbeddedObject([
            new ChildEntity(1),
            new ChildEntity(2),
            new ChildEntity(3),
        ])), $actual);
    }

    public function testRemove()
    {
        $this->setDataInDb([
            'entities' => [
                ['id' => 1],
            ],
            'parent_children' => [
                ['id' => 1, 'parent_id' => 1, 'child_id' => 1],
                ['id' => 2, 'parent_id' => 1, 'child_id' => 2],
                ['id' => 3, 'parent_id' => 1, 'child_id' => 3],
            ],
            'children' => [
                ['id' => 1],
                ['id' => 2],
                ['id' => 3],
            ],
        ]);

        $this->repo->removeById(1);

        $this->assertDatabaseDataSameAs([
            'entities' => [
            ],
            'parent_children' => [
            ],
            'children' => [
                ['id' => 1],
                ['id' => 2],
                ['id' => 3],
            ],
        ]);
    }
}
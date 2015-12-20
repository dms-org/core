<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping;

use Dms\Core\Persistence\Db\Mapping\IOrm;
use Dms\Core\Persistence\Db\Schema\Column;
use Dms\Core\Persistence\Db\Schema\ForeignKey;
use Dms\Core\Persistence\Db\Schema\ForeignKeyMode;
use Dms\Core\Persistence\Db\Schema\Type\Integer;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObjectWithToManyRelation\ChildEntity;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObjectWithToManyRelation\EmbeddedObject;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObjectWithToManyRelation\EntityWithValueObject;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObjectWithToManyRelation\EntityWithValueObjectMapper;
use Dms\Core\Tests\Persistence\Db\Mock\MockDatabase;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ValueObjectWithToManyRelationTest extends DbIntegrationTest
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
                'fk_children_parent_id_entities',
                    ['parent_id'],
                    'entities',
                    ['id'],
                    ForeignKeyMode::CASCADE,
                    ForeignKeyMode::CASCADE
            )
        ], array_values($this->db->getTable('children')->getStructure()->getForeignKeys()));
    }

    public function testCorrectTableLayout()
    {
        $this->assertDatabaseStructureSameAs([
                'entities' => [
                        new Column('id', Integer::normal()->autoIncrement(), true),
                ],
                'children' => [
                        new Column('id', Integer::normal()->autoIncrement(), true),
                        new Column('parent_id', Integer::normal()),
                ]
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
                        ['id' => 1]
                ],
                'children' => [
                        ['id' => 1, 'parent_id' => 1],
                        ['id' => 2, 'parent_id' => 1],
                        ['id' => 3, 'parent_id' => 1],
                ],
        ]);
    }

    public function testLoad()
    {
        $this->db->setData([
                'entities' => [
                        ['id' => 1]
                ],
                'children' => [
                        ['id' => 1, 'parent_id' => 1],
                        ['id' => 2, 'parent_id' => 1],
                        ['id' => 3, 'parent_id' => 1],
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
        $this->db->setData([
                'entities' => [
                        ['id' => 1]
                ],
                'children' => [
                        ['id' => 1, 'parent_id' => 1],
                        ['id' => 2, 'parent_id' => 1],
                        ['id' => 3, 'parent_id' => 1],
                ],
        ]);

        $this->repo->removeById(1);

        $this->assertDatabaseDataSameAs([
                'entities' => [
                ],
                'children' => [
                ],
        ]);
    }
}
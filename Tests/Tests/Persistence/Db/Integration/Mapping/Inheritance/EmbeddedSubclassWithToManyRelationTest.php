<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Inheritance;

use Iddigital\Cms\Core\Persistence\Db\Mapping\IOrm;
use Iddigital\Cms\Core\Persistence\Db\Schema\Column;
use Iddigital\Cms\Core\Persistence\Db\Schema\ForeignKey;
use Iddigital\Cms\Core\Persistence\Db\Schema\ForeignKeyMode;
use Iddigital\Cms\Core\Persistence\Db\Schema\Type\Enum;
use Iddigital\Cms\Core\Persistence\Db\Schema\Type\Integer;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\DbIntegrationTest;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Inheritance\Fixtures\EmbeddedSubclassWithToManyRelation\ChildEntity;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Inheritance\Fixtures\EmbeddedSubclassWithToManyRelation\EntitySubclass;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Inheritance\Fixtures\EmbeddedSubclassWithToManyRelation\RootEntityMapper;
use Iddigital\Cms\Core\Tests\Persistence\Db\Mock\MockDatabase;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EmbeddedSubclassWithToManyRelationTest extends DbIntegrationTest
{
    /**
     * @inheritDoc
     */
    protected function loadOrm()
    {
        return RootEntityMapper::orm();
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
                        new Column('type', (new Enum(['subclass']))->nullable()),
                ],
                'children' => [
                        new Column('id', Integer::normal()->autoIncrement(), true),
                        new Column('parent_id', Integer::normal()),
                ]
        ]);
    }

    public function testPersist()
    {
        $entity = new EntitySubclass(null, [
                new ChildEntity(),
                new ChildEntity(),
                new ChildEntity(),
        ]);

        $this->repo->save($entity);

        $this->assertDatabaseDataSameAs([
                'entities' => [
                        ['id' => 1, 'type' => 'subclass'],
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
                        ['id' => 1, 'type' => 'subclass'],
                ],
                'children' => [
                        ['id' => 1, 'parent_id' => 1],
                        ['id' => 2, 'parent_id' => 1],
                        ['id' => 3, 'parent_id' => 1],
                ],
        ]);

        $actual = $this->repo->get(1);

        $this->assertEquals(new EntitySubclass(1, [
                new ChildEntity(1),
                new ChildEntity(2),
                new ChildEntity(3),
        ]), $actual);
    }

    public function testRemove()
    {
        $this->db->setData([
                'entities' => [
                        ['id' => 1, 'type' => 'subclass'],
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
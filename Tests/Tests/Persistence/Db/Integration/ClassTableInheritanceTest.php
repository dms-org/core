<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration;

use Iddigital\Cms\Core\Persistence\Db\Mapping\IEntityMapper;
use Iddigital\Cms\Core\Persistence\Db\Schema\Column;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;
use Iddigital\Cms\Core\Persistence\Db\Schema\Type\Boolean;
use Iddigital\Cms\Core\Persistence\Db\Schema\Type\Integer;
use Iddigital\Cms\Core\Persistence\Db\Schema\Type\Varchar;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\TableInheritance\ClassPerTable\TestClassTableInheritanceMapper;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\TableInheritance\TestSubclassEntity1;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\TableInheritance\TestSubclassEntity2;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\TableInheritance\TestSubclassEntity3;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\TableInheritance\TestSuperclassEntity;
use Iddigital\Cms\Core\Tests\Persistence\Db\Mock\MockDatabase;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ClassTableInheritanceTest extends DbIntegrationTest
{
    /**
     * @var Table
     */
    private $parentEntities;

    /**
     * @return IEntityMapper
     */
    protected function loadMapper()
    {
        return new TestClassTableInheritanceMapper('parent_entities');
    }

    public function setUp()
    {
        parent::setUp();
        $this->parentEntities = $this->getSchemaTable('parent_entities');
    }

    /**
     * @inheritDoc
     */
    protected function buildDatabase(MockDatabase $db, IEntityMapper $mapper)
    {
        parent::buildDatabase($db, $mapper);

        $db->createForeignKey('subclass1_table.id', 'parent_entities.id');

        $db->createForeignKey('subclass3_table.id', 'subclass1_table.id');

        $db->createForeignKey('subclass2_table.id', 'parent_entities.id');
    }

    public function testBuildsCorrectTables()
    {
        $this->assertDatabaseStructureSameAs([
                'parent_entities' => [
                        new Column('id', Integer::normal()->autoIncrement(), true),
                        new Column('base_prop', new Varchar(255)),
                ],
                'subclass1_table' => [
                        new Column('id', Integer::normal()->autoIncrement(), true),
                        new Column('subclass1_prop', Integer::normal()),
                ],
                'subclass2_table' => [
                        new Column('id', Integer::normal()->autoIncrement(), true),
                        new Column('subclass2_prop', Integer::normal()),
                        new Column('subclass2_prop2', new Boolean()),
                ],
                'subclass3_table' => [
                        new Column('id', Integer::normal()->autoIncrement(), true),
                        new Column('subclass3_prop', new Varchar(255)),
                ],
        ]);
    }

    public function testPersistEntities()
    {
        $this->repo->saveAll([
                new TestSuperclassEntity(1, 'base1-parent'),
                new TestSubclassEntity1(null, 'base1', 100),
                new TestSubclassEntity2(null, 'base2', 123, false),
                new TestSubclassEntity3(null, 'base3', 99, 'sub3'),
        ]);

        $this->assertDatabaseDataSameAs([
                'parent_entities' => [
                        ['id' => 1, 'base_prop' => 'base1-parent'],
                        ['id' => 2, 'base_prop' => 'base1'],
                        ['id' => 3, 'base_prop' => 'base2'],
                        ['id' => 4, 'base_prop' => 'base3'],
                ],
                'subclass1_table' => [
                        ['id' => 2, 'subclass1_prop' => 100],
                        ['id' => 4, 'subclass1_prop' => 99],
                ],
                'subclass2_table' => [
                        ['id' => 3, 'subclass2_prop' => 123, 'subclass2_prop2' => false],
                ],
                'subclass3_table' => [
                        ['id' => 4, 'subclass3_prop' => 'sub3'],
                ],
        ]);
    }

    public function testPersistExisting()
    {
        $this->db->setData([
                'parent_entities' => [
                        ['id' => 1, 'base_prop' => 'base1-parent'],
                        ['id' => 2, 'base_prop' => 'base1'],
                        ['id' => 3, 'base_prop' => 'base2'],
                        ['id' => 4, 'base_prop' => 'base3'],
                ],
                'subclass1_table' => [
                        ['id' => 2, 'subclass1_prop' => 100],
                        ['id' => 4, 'subclass1_prop' => 99],
                ],
                'subclass2_table' => [
                        ['id' => 3, 'subclass2_prop' => 123, 'subclass2_prop2' => false],
                ],
                'subclass3_table' => [
                        ['id' => 4, 'subclass3_prop' => 'sub3'],
                ],
        ]);

        $this->repo->saveAll([
                new TestSuperclassEntity(1, 'base1-parent1'),
                new TestSubclassEntity1(2, 'base1-', 200),
                new TestSubclassEntity2(3, 'base2-', -123, true),
                new TestSubclassEntity3(4, 'base3-', 500, '--sub3--'),
        ]);

        $this->assertDatabaseDataSameAs([
                'parent_entities' => [
                        ['id' => 1, 'base_prop' => 'base1-parent1'],
                        ['id' => 2, 'base_prop' => 'base1-'],
                        ['id' => 3, 'base_prop' => 'base2-'],
                        ['id' => 4, 'base_prop' => 'base3-'],
                ],
                'subclass1_table' => [
                        ['id' => 2, 'subclass1_prop' => 200],
                        ['id' => 4, 'subclass1_prop' => 500],
                ],
                'subclass2_table' => [
                        ['id' => 3, 'subclass2_prop' => -123, 'subclass2_prop2' => true],
                ],
                'subclass3_table' => [
                        ['id' => 4, 'subclass3_prop' => '--sub3--'],
                ],
        ]);
    }

    public function testLoadEntities()
    {
        $this->db->setData([
                'parent_entities' => [
                        ['id' => 1, 'base_prop' => 'base1-parent'],
                        ['id' => 2, 'base_prop' => 'base1'],
                        ['id' => 3, 'base_prop' => 'base2'],
                        ['id' => 4, 'base_prop' => 'base3'],
                ],
                'subclass1_table' => [
                        ['id' => 2, 'subclass1_prop' => 100],
                        ['id' => 4, 'subclass1_prop' => 99],
                ],
                'subclass2_table' => [
                        ['id' => 3, 'subclass2_prop' => 123, 'subclass2_prop2' => false],
                ],
                'subclass3_table' => [
                        ['id' => 4, 'subclass3_prop' => 'sub3'],
                ],
        ]);

        $this->assertEquals([
                new TestSuperclassEntity(1, 'base1-parent'),
                new TestSubclassEntity1(2, 'base1', 100),
                new TestSubclassEntity2(3, 'base2', 123, false),
                new TestSubclassEntity3(4, 'base3', 99, 'sub3'),
        ], $this->repo->getAll());
    }

    public function testRemoveEntities()
    {
        $this->db->setData([
                'parent_entities' => [
                        ['id' => 1, 'base_prop' => 'base1-parent'],
                        ['id' => 2, 'base_prop' => 'base1'],
                        ['id' => 3, 'base_prop' => 'base2'],
                        ['id' => 4, 'base_prop' => 'base3'],
                ],
                'subclass1_table' => [
                        ['id' => 2, 'subclass1_prop' => 100],
                        ['id' => 4, 'subclass1_prop' => 99],
                ],
                'subclass2_table' => [
                        ['id' => 3, 'subclass2_prop' => 123, 'subclass2_prop2' => false],
                ],
                'subclass3_table' => [
                        ['id' => 4, 'subclass3_prop' => 'sub3'],
                ],
        ]);

        $this->repo->removeAllById([1, 2, 3, 4]);

        $this->assertDatabaseDataSameAs([
                'parent_entities' => [
                ],
                'subclass1_table' => [
                ],
                'subclass2_table' => [
                ],
                'subclass3_table' => [
                ],
        ]);
    }
}
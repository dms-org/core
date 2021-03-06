<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Inheritance;

use Dms\Core\Persistence\Db\Mapping\CustomOrm;
use Dms\Core\Persistence\Db\Mapping\IOrm;
use Dms\Core\Persistence\Db\Schema\Column;
use Dms\Core\Persistence\Db\Schema\ForeignKey;
use Dms\Core\Persistence\Db\Schema\ForeignKeyMode;
use Dms\Core\Persistence\Db\Schema\PrimaryKeyBuilder;
use Dms\Core\Persistence\Db\Schema\Table;
use Dms\Core\Persistence\Db\Schema\Type\Boolean;
use Dms\Core\Persistence\Db\Schema\Type\Integer;
use Dms\Core\Persistence\Db\Schema\Type\Varchar;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\DbIntegrationTest;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Inheritance\Fixtures\TableInheritance\ClassPerTable\TestClassTableInheritanceMapper;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Inheritance\Fixtures\TableInheritance\TestSubclassEntity1;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Inheritance\Fixtures\TableInheritance\TestSubclassEntity2;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Inheritance\Fixtures\TableInheritance\TestSubclassEntity3;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Inheritance\Fixtures\TableInheritance\TestSuperclassEntity;

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
     * @return IOrm
     */
    protected function loadOrm()
    {
        return CustomOrm::from([TestSuperclassEntity::class => TestClassTableInheritanceMapper::class]);
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->parentEntities = $this->getSchemaTable('parent_entities');
    }

    public function testCreatesCorrectForeignKeys()
    {
        $this->assertEquals([
                new ForeignKey(
                        'fk_subclass1_table_id_parent_entities',
                        ['id'],
                        'parent_entities',
                        ['id'],
                        ForeignKeyMode::CASCADE,
                        ForeignKeyMode::CASCADE
                )
        ], array_values($this->getSchemaTable('subclass1_table')->getForeignKeys()));

        $this->assertEquals([
                new ForeignKey(
                        'fk_subclass3_table_id_subclass1_table',
                        ['id'],
                        'subclass1_table',
                        ['id'],
                        ForeignKeyMode::CASCADE,
                        ForeignKeyMode::CASCADE
                )
        ], array_values($this->getSchemaTable('subclass3_table')->getForeignKeys()));

        $this->assertEquals([
                new ForeignKey(
                        'fk_subclass2_table_id_parent_entities',
                        ['id'],
                        'parent_entities',
                        ['id'],
                        ForeignKeyMode::CASCADE,
                        ForeignKeyMode::CASCADE
                )
        ], array_values($this->getSchemaTable('subclass2_table')->getForeignKeys()));
    }

    public function testBuildsCorrectTables()
    {
        $this->assertDatabaseStructureSameAs([
                'parent_entities' => [
                        PrimaryKeyBuilder::incrementingInt('id'),
                        new Column('base_prop', new Varchar(255)),
                ],
                'subclass1_table' => [
                        PrimaryKeyBuilder::incrementingInt('id'),
                        new Column('subclass1_prop', Integer::normal()),
                ],
                'subclass2_table' => [
                        PrimaryKeyBuilder::incrementingInt('id'),
                        new Column('subclass2_prop', Integer::normal()),
                        new Column('subclass2_prop2', new Boolean()),
                ],
                'subclass3_table' => [
                        PrimaryKeyBuilder::incrementingInt('id'),
                        new Column('subclass3_prop', new Varchar(255)),
                ],
        ]);
    }

    public function testPersistEntities()
    {
        $this->repo->saveAll([
                new TestSuperclassEntity(null, 'base1-parent'),
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
        $this->setDataInDb([
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
        $this->setDataInDb([
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
        $this->setDataInDb([
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
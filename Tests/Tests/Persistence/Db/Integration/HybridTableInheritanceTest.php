<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration;

use Iddigital\Cms\Core\Persistence\Db\Mapping\CustomOrm;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IEntityMapper;
use Iddigital\Cms\Core\Persistence\Db\Schema\Column;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;
use Iddigital\Cms\Core\Persistence\Db\Schema\Type\Boolean;
use Iddigital\Cms\Core\Persistence\Db\Schema\Type\Enum;
use Iddigital\Cms\Core\Persistence\Db\Schema\Type\Integer;
use Iddigital\Cms\Core\Persistence\Db\Schema\Type\Varchar;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\TableInheritance\Hybrid\TestHybridTableInheritanceMapper;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\TableInheritance\TestSubclassEntity1;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\TableInheritance\TestSubclassEntity2;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\TableInheritance\TestSubclassEntity3;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\TableInheritance\TestSuperclassEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class HybridTableInheritanceTest extends DbIntegrationTest
{
    /**
     * @var Table
     */
    private $parentEntities;

    /**
     * @inheritDoc
     */
    protected function loadOrm()
    {
        return CustomOrm::from([TestSuperclassEntity::class => TestHybridTableInheritanceMapper::class]);
    }

    public function setUp()
    {
        parent::setUp();
        $this->parentEntities = $this->getSchemaTable('parent_entities');
    }

    public function testBuildsCorrectTable()
    {
        $this->assertDatabaseStructureSameAs([
                'parent_entities' => [
                        new Column('id', Integer::normal()->autoIncrement(), true),
                        new Column('class_type', (new Enum(['subclass1', 'subclass3']))->nullable()),
                        new Column('base_prop', new Varchar(255)),
                        new Column('subclass1_prop', Integer::normal()->nullable()),
                        new Column('subclass3_prop', (new Varchar(255))->nullable()),
                ],
                'subclass2_table' => [
                        new Column('id', Integer::normal()->autoIncrement(), true),
                        new Column('subclass2_prop', Integer::normal()),
                        new Column('subclass2_prop2', new Boolean()),
                ]
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
                        [
                                'id'             => 1,
                                'class_type'     => null,
                                'base_prop'      => 'base1-parent',
                                'subclass1_prop' => null,
                                'subclass3_prop' => null
                        ],
                        [
                                'id'             => 2,
                                'class_type'     => 'subclass1',
                                'base_prop'      => 'base1',
                                'subclass1_prop' => 100,
                                'subclass3_prop' => null
                        ],
                        [
                                'id'             => 3,
                                'class_type'     => null,
                                'base_prop'      => 'base2',
                                'subclass1_prop' => null,
                                'subclass3_prop' => null,
                        ],
                        [
                                'id'             => 4,
                                'class_type'     => 'subclass3',
                                'base_prop'      => 'base3',
                                'subclass1_prop' => 99,
                                'subclass3_prop' => 'sub3',
                        ],
                ],
                'subclass2_table' => [
                        ['id' => 3, 'subclass2_prop' => 123, 'subclass2_prop2' => false],
                ]
        ]);
    }

    public function testLoadEntities()
    {
        $this->db->setData([
                'parent_entities' => [
                        [
                                'id'             => 1,
                                'class_type'     => null,
                                'base_prop'      => 'base1-parent',
                                'subclass1_prop' => null,
                                'subclass3_prop' => null
                        ],
                        [
                                'id'             => 2,
                                'class_type'     => 'subclass1',
                                'base_prop'      => 'base1',
                                'subclass1_prop' => 100,
                                'subclass3_prop' => null
                        ],
                        [
                                'id'             => 3,
                                'class_type'     => null,
                                'base_prop'      => 'base2',
                                'subclass1_prop' => null,
                                'subclass3_prop' => null,
                        ],
                        [
                                'id'             => 4,
                                'class_type'     => 'subclass3',
                                'base_prop'      => 'base3',
                                'subclass1_prop' => 99,
                                'subclass3_prop' => 'sub3',
                        ],
                ],
                'subclass2_table' => [
                        ['id' => 3, 'subclass2_prop' => 123, 'subclass2_prop2' => false],
                ]
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
                        [
                                'id'             => 1,
                                'class_type'     => 'subclass1',
                                'base_prop'      => 'base1',
                                'subclass1_prop' => 100,
                                'subclass3_prop' => null
                        ],
                        [
                                'id'             => 2,
                                'class_type'     => null,
                                'base_prop'      => 'base2',
                                'subclass1_prop' => null,
                                'subclass3_prop' => null,
                        ],
                        [
                                'id'             => 3,
                                'class_type'     => 'subclass3',
                                'base_prop'      => 'base3',
                                'subclass1_prop' => 99,
                                'subclass3_prop' => 'sub3',
                        ],
                ],
                'subclass2_table' => [
                        ['id' => 2, 'subclass2_prop' => 123, 'subclass2_prop2' => false],
                ]
        ]);

        $this->repo->removeAllById([1, 2]);

        $this->assertDatabaseDataSameAs([
                'parent_entities' => [
                        [
                                'id'             => 3,
                                'class_type'     => 'subclass3',
                                'base_prop'      => 'base3',
                                'subclass1_prop' => 99,
                                'subclass3_prop' => 'sub3',
                        ],
                ],
                'subclass2_table' => [
                ]
        ]);
    }
}
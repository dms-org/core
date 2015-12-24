<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Inheritance;

use Dms\Core\Persistence\Db\Mapping\CustomOrm;
use Dms\Core\Persistence\Db\Schema\Column;
use Dms\Core\Persistence\Db\Schema\Table;
use Dms\Core\Persistence\Db\Schema\Type\Boolean;
use Dms\Core\Persistence\Db\Schema\Type\Enum;
use Dms\Core\Persistence\Db\Schema\Type\Integer;
use Dms\Core\Persistence\Db\Schema\Type\Varchar;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\DbIntegrationTest;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Inheritance\Fixtures\TableInheritance\SingleTable\TestSingleTableInheritanceMapper;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Inheritance\Fixtures\TableInheritance\TestSubclassEntity1;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Inheritance\Fixtures\TableInheritance\TestSubclassEntity2;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Inheritance\Fixtures\TableInheritance\TestSubclassEntity3;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Inheritance\Fixtures\TableInheritance\TestSuperclassEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class SingleTableInheritanceTest extends DbIntegrationTest
{
    /**
     * @var Table
     */
    private $entities;

    /**
     * @inheritDoc
     */
    protected function loadOrm()
    {
        return CustomOrm::from([TestSuperclassEntity::class => TestSingleTableInheritanceMapper::class]);
    }

    public function setUp()
    {
        parent::setUp();
        $this->entities = $this->getSchemaTable('entities');
    }

    public function testBuildsCorrectTable()
    {
        $this->assertDatabaseStructureSameAs([
                'entities' => [
                        new Column('id', Integer::normal()->autoIncrement(), true),
                        new Column('class_type', new Enum(['subclass1', 'subclass2', 'subclass3'])),
                        new Column('base_prop', new Varchar(255)),
                        new Column('subclass1_prop', Integer::normal()->nullable()),
                        new Column('subclass3_prop', (new Varchar(255))->nullable()),
                        new Column('subclass2_prop', Integer::normal()->nullable()),
                        new Column('subclass2_prop2', (new Boolean())->nullable()),
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
                'entities' => [
                        [
                                'id'              => 1,
                                'class_type'      => null,
                                'base_prop'       => 'base1-parent',
                                'subclass1_prop'  => null,
                                'subclass3_prop'  => null,
                                'subclass2_prop'  => null,
                                'subclass2_prop2' => null
                        ],
                        [
                                'id'              => 2,
                                'class_type'      => 'subclass1',
                                'base_prop'       => 'base1',
                                'subclass1_prop'  => 100,
                                'subclass3_prop'  => null,
                                'subclass2_prop'  => null,
                                'subclass2_prop2' => null
                        ],
                        [
                                'id'              => 3,
                                'class_type'      => 'subclass2',
                                'base_prop'       => 'base2',
                                'subclass1_prop'  => null,
                                'subclass3_prop'  => null,
                                'subclass2_prop'  => 123,
                                'subclass2_prop2' => false
                        ],
                        [
                                'id'              => 4,
                                'class_type'      => 'subclass3',
                                'base_prop'       => 'base3',
                                'subclass1_prop'  => 99,
                                'subclass3_prop'  => 'sub3',
                                'subclass2_prop'  => null,
                                'subclass2_prop2' => null
                        ],
                ]
        ]);
    }

    public function testLoadEntities()
    {
        $this->db->setData([
                'entities' => [
                        [
                                'id'              => 1,
                                'class_type'      => null,
                                'base_prop'       => 'base1-parent',
                                'subclass1_prop'  => null,
                                'subclass3_prop'  => null,
                                'subclass2_prop'  => null,
                                'subclass2_prop2' => null
                        ],
                        [
                                'id'              => 2,
                                'class_type'      => 'subclass1',
                                'base_prop'       => 'base1',
                                'subclass1_prop'  => 100,
                                'subclass3_prop'  => null,
                                'subclass2_prop'  => null,
                                'subclass2_prop2' => null
                        ],
                        [
                                'id'              => 3,
                                'class_type'      => 'subclass2',
                                'base_prop'       => 'base2',
                                'subclass1_prop'  => null,
                                'subclass3_prop'  => null,
                                'subclass2_prop'  => 123,
                                'subclass2_prop2' => false
                        ],
                        [
                                'id'              => 4,
                                'class_type'      => 'subclass3',
                                'base_prop'       => 'base3',
                                'subclass1_prop'  => 99,
                                'subclass3_prop'  => 'sub3',
                                'subclass2_prop'  => null,
                                'subclass2_prop2' => null
                        ],
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
                'entities' => [
                        [
                                'id'              => 1,
                                'class_type'      => null,
                                'base_prop'       => 'base1-parent',
                                'subclass1_prop'  => null,
                                'subclass3_prop'  => null,
                                'subclass2_prop'  => null,
                                'subclass2_prop2' => null
                        ],
                        [
                                'id'              => 2,
                                'class_type'      => 'subclass1',
                                'base_prop'       => 'base1',
                                'subclass1_prop'  => 100,
                                'subclass3_prop'  => null,
                                'subclass2_prop'  => null,
                                'subclass2_prop2' => null
                        ],
                        [
                                'id'              => 3,
                                'class_type'      => 'subclass2',
                                'base_prop'       => 'base2',
                                'subclass1_prop'  => null,
                                'subclass3_prop'  => null,
                                'subclass2_prop'  => 123,
                                'subclass2_prop2' => false
                        ],
                        [
                                'id'              => 4,
                                'class_type'      => 'subclass3',
                                'base_prop'       => 'base3',
                                'subclass1_prop'  => 99,
                                'subclass3_prop'  => 'sub3',
                                'subclass2_prop'  => null,
                                'subclass2_prop2' => null
                        ],
                ]
        ]);

        $this->repo->removeAllById([1, 2, 3]);

        $this->assertDatabaseDataSameAs([
                'entities' => [
                        [
                                'id'              => 4,
                                'class_type'      => 'subclass3',
                                'base_prop'       => 'base3',
                                'subclass1_prop'  => 99,
                                'subclass3_prop'  => 'sub3',
                                'subclass2_prop'  => null,
                                'subclass2_prop2' => null
                        ],
                ]
        ]);
    }
}
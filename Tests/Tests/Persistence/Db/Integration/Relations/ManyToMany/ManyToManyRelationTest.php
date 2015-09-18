<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Relations\ManyToMany;

use Iddigital\Cms\Core\Persistence\Db\Mapping\IEntityMapper;
use Iddigital\Cms\Core\Persistence\Db\Query\Clause\Join;
use Iddigital\Cms\Core\Persistence\Db\Query\Delete;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\Expr;
use Iddigital\Cms\Core\Persistence\Db\Query\Select;
use Iddigital\Cms\Core\Persistence\Db\Query\Upsert;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\DbIntegrationTest;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\ManyToManyRelation\AnotherEntity;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\ManyToManyRelation\OneEntity;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\ManyToManyRelation\OneEntityMapper;
use Iddigital\Cms\Core\Tests\Persistence\Db\Mock\MockDatabase;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ManyToManyRelationTest extends DbIntegrationTest
{
    /**
     * @var Table
     */
    protected $oneTable;

    /**
     * @var Table
     */
    protected $joinTable;

    /**
     * @var Table
     */
    protected $anotherTable;

    /**
     * @inheritDoc
     */
    protected function loadMapper()
    {
        return new OneEntityMapper();
    }

    /**
     * {@inheritDoc}
     */
    protected function buildDatabase(MockDatabase $db, IEntityMapper $mapper)
    {
        parent::buildDatabase($db, $mapper);
        $db->createForeignKey('one_anothers.one_id', 'ones.id');
        $db->createForeignKey('one_anothers.another_id', 'anothers.id');
        $this->oneTable     = $db->getTable('ones')->getStructure();
        $this->joinTable    = $db->getTable('one_anothers')->getStructure();
        $this->anotherTable = $db->getTable('anothers')->getStructure();
    }

    public function testPersistNoChildren()
    {
        $entity = new OneEntity();

        $this->repo->save($entity);

        $this->assertDatabaseDataSameAs([
                'ones'         => [
                        ['id' => 1]
                ],
                'anothers'     => [],
                'one_anothers' => [],
        ]);

        $this->assertExecutedQueryTypes([
                'Insert entities' => Upsert::class,
        ]);
    }

    public function testPersistMultipleWithSharedChildren()
    {
        $another1 = new AnotherEntity(null, 1);
        $another2 = new AnotherEntity(null, 2);
        $another3 = new AnotherEntity(null, 3);
        $entities = [
                new OneEntity(null, [
                        $another1,
                        $another2,
                        $another3,
                ]),
                new OneEntity(null, [
                        $another1,
                        $another3,
                ]),
                new OneEntity(null, [
                        $another2,
                        $another3,
                ]),
        ];

        $this->repo->saveAll($entities);

        $this->assertDatabaseDataSameAs([
                'ones'         => [
                        ['id' => 1],
                        ['id' => 2],
                        ['id' => 3],
                ],
                'anothers'     => [
                        ['id' => 1, 'val' => 1],
                        ['id' => 2, 'val' => 2],
                        ['id' => 3, 'val' => 3],
                ],
                'one_anothers' => [
                        ['one_id' => 1, 'another_id' => 1],
                        ['one_id' => 1, 'another_id' => 2],
                        ['one_id' => 1, 'another_id' => 3],
                        ['one_id' => 2, 'another_id' => 1],
                        ['one_id' => 2, 'another_id' => 3],
                        ['one_id' => 3, 'another_id' => 2],
                        ['one_id' => 3, 'another_id' => 3],
                ],
        ]);

        $this->assertExecutedQueryTypes([
                'Insert one entities'   => Upsert::class,
                'Insert other entities' => Upsert::class,
                'Insert join rows'      => Upsert::class,
        ]);
    }

    public function testPersistExisting()
    {
        $this->db->setData([
                'ones'         => [
                        ['id' => 1],
                ],
                'anothers'     => [
                        ['id' => 1, 'val' => 1],
                        ['id' => 2, 'val' => 2],
                        ['id' => 3, 'val' => 3],
                ],
                'one_anothers' => [
                        ['one_id' => 1, 'another_id' => 1],
                        ['one_id' => 1, 'another_id' => 2],
                        ['one_id' => 1, 'another_id' => 3],
                ],
        ]);

        $entity = new OneEntity(1, [
                new AnotherEntity(null, 1),
                new AnotherEntity(null, 2),
                new AnotherEntity(2, 3),
        ]);

        $this->repo->save($entity);

        $this->assertDatabaseDataSameAs([
                'ones'         => [
                        ['id' => 1],
                ],
                'anothers'     => [
                        ['id' => 1, 'val' => 1],
                        ['id' => 2, 'val' => 3],
                        ['id' => 3, 'val' => 3],
                        ['id' => 4, 'val' => 1],
                        ['id' => 5, 'val' => 2],
                ],
                'one_anothers' => [
                        ['one_id' => 1, 'another_id' => 4],
                        ['one_id' => 1, 'another_id' => 5],
                        ['one_id' => 1, 'another_id' => 2],
                ],
        ]);

        $this->assertExecutedQueryTypes([
                'Insert one entities'               => Upsert::class,
                'Insert other entities'             => Upsert::class,
                'Clear previous join rows entities' => Delete::class,
                'Insert join rows'                  => Upsert::class,
        ]);
    }

    public function testLoadWithNoChildren()
    {
        $this->db->setData([
                'ones'         => [
                        ['id' => 1]
                ],
                'anothers'     => [],
                'one_anothers' => [],
        ]);

        $this->assertEquals(new OneEntity(1), $this->repo->get(1));

        $this->assertExecutedQueryTypes([
                'Select one entity'              => Select::class,
                'Select related entities (none)' => Select::class,
        ]);

        $this->assertExecutedQueryNumber(2, Select::from($this->anotherTable)
                ->addRawColumn('id')
                ->addRawColumn('val')
                ->addColumn('one_id', Expr::tableColumn($this->joinTable, 'one_id'))
                ->join(Join::right($this->joinTable, $this->joinTable->getName(), [
                        Expr::equal(Expr::tableColumn($this->joinTable, 'another_id'), Expr::tableColumn($this->anotherTable, 'id'))
                ]))
                ->where(Expr::in(Expr::tableColumn($this->joinTable, 'one_id'), Expr::tuple([Expr::idParam(1)])))
        );
    }

    public function testLoadWithDuplicates()
    {
        $this->db->setData([
                'ones'         => [
                        ['id' => 1]
                ],
                'anothers'     => [
                        ['id' => 1, 'val' => 1],
                ],
                'one_anothers' => [
                        ['one_id' => 1, 'another_id' => 1],
                        ['one_id' => 1, 'another_id' => 1],
                ],
        ]);

        /** @var OneEntity $actual */
        $actual = $this->repo->get(1);
        $this->assertEquals(new OneEntity(1, [
                $another = new AnotherEntity(1, 1),
                $another,
        ]), $actual);
        $this->assertSame($actual->others[0], $actual->others[1]);

        $this->assertExecutedQueryTypes([
                'Select one entity'              => Select::class,
                'Select related entities (none)' => Select::class,
        ]);
    }

    /**
     * @return void
     */
    public function testLoadWithSharedChildren()
    {
        $this->db->setData([
                'ones'         => [
                        ['id' => 1],
                        ['id' => 2],
                        ['id' => 3],
                ],
                'anothers'     => [
                        ['id' => 1, 'val' => 1],
                        ['id' => 2, 'val' => 2],
                        ['id' => 3, 'val' => 3],
                ],
                'one_anothers' => [
                        ['one_id' => 1, 'another_id' => 1],
                        ['one_id' => 1, 'another_id' => 2],
                        ['one_id' => 1, 'another_id' => 3],
                        ['one_id' => 2, 'another_id' => 1],
                        ['one_id' => 2, 'another_id' => 3],
                        ['one_id' => 3, 'another_id' => 2],
                        ['one_id' => 3, 'another_id' => 3],
                ],
        ]);

        $another1 = new AnotherEntity(1, 1);
        $another2 = new AnotherEntity(2, 2);
        $another3 = new AnotherEntity(3, 3);
        $entities = [
                new OneEntity(1, [
                        $another1,
                        $another2,
                        $another3,
                ]),
                new OneEntity(2, [
                        $another1,
                        $another3,
                ]),
                new OneEntity(3, [
                        $another2,
                        $another3,
                ]),
        ];

        /** @var OneEntity[] $actual */
        $actual = $this->repo->getAll();
        $this->assertEquals($entities, $actual);
        $this->assertSame($actual[0]->others[0], $actual[1]->others[0]);

        $this->assertExecutedQueryTypes([
                'Load parent entities' => Select::class,
                'Load child entities'  => Select::class,
        ]);

        $this->assertExecutedQueryNumber(2, Select::from($this->anotherTable)
                ->addRawColumn('id')
                ->addRawColumn('val')
                ->addColumn('one_id', Expr::tableColumn($this->joinTable, 'one_id'))
                ->join(Join::right($this->joinTable, $this->joinTable->getName(), [
                        Expr::equal(Expr::tableColumn($this->joinTable, 'another_id'), Expr::tableColumn($this->anotherTable, 'id'))
                ]))
                ->where(Expr::in(Expr::tableColumn($this->joinTable, 'one_id'),
                        Expr::tuple([Expr::idParam(1), Expr::idParam(2), Expr::idParam(3)])))
        );
    }

    /**
     * @return void
     */
    public function testRemove()
    {
        $this->db->setData([
                'ones'         => [
                        ['id' => 1],
                        ['id' => 2],
                        ['id' => 3],
                ],
                'anothers'     => [
                        ['id' => 1, 'val' => 1],
                        ['id' => 2, 'val' => 2],
                        ['id' => 3, 'val' => 3],
                ],
                'one_anothers' => [
                        ['one_id' => 1, 'another_id' => 1],
                        ['one_id' => 1, 'another_id' => 2],
                        ['one_id' => 1, 'another_id' => 3],
                        ['one_id' => 2, 'another_id' => 1],
                        ['one_id' => 2, 'another_id' => 3],
                        ['one_id' => 3, 'another_id' => 2],
                        ['one_id' => 3, 'another_id' => 3],
                ],
        ]);

        /** @var OneEntity[] $actual */
        $this->repo->removeAllById([1, 3]);

        $this->assertDatabaseDataSameAs([
                'ones'         => [
                        ['id' => 2],
                ],
                'anothers'     => [
                        ['id' => 1, 'val' => 1],
                        ['id' => 2, 'val' => 2],
                        ['id' => 3, 'val' => 3],
                ],
                'one_anothers' => [
                        ['one_id' => 2, 'another_id' => 1],
                        ['one_id' => 2, 'another_id' => 3],
                ],
        ]);

        $this->assertExecutedQueryTypes([
                'Delete one entities'     => Delete::class,
                'Delete related entities' => Delete::class,
        ]);
    }
}
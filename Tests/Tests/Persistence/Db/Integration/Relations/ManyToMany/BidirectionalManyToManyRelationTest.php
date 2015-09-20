<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Relations\ManyToMany;

use Iddigital\Cms\Core\Persistence\Db\Mapping\IEntityMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IOrm;
use Iddigital\Cms\Core\Persistence\Db\Query\Clause\Join;
use Iddigital\Cms\Core\Persistence\Db\Query\Delete;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\Expr;
use Iddigital\Cms\Core\Persistence\Db\Query\Select;
use Iddigital\Cms\Core\Persistence\Db\Query\Upsert;
use Iddigital\Cms\Core\Persistence\Db\Schema\ForeignKey;
use Iddigital\Cms\Core\Persistence\Db\Schema\ForeignKeyMode;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\DbIntegrationTest;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\ManyToManyRelation\Bidirectional\AnotherEntity;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\ManyToManyRelation\Bidirectional\OneEntity;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\ManyToManyRelation\Bidirectional\OneEntityMapper;
use Iddigital\Cms\Core\Tests\Persistence\Db\Mock\MockDatabase;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class BidirectionalManyToManyRelationTest extends DbIntegrationTest
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
    protected function loadOrm()
    {
        return OneEntityMapper::orm();
    }

    /**
     * {@inheritDoc}
     */
    protected function buildDatabase(MockDatabase $db, IOrm $orm)
    {
        parent::buildDatabase($db, $orm);
        $this->oneTable     = $db->getTable('ones')->getStructure();
        $this->joinTable    = $db->getTable('one_anothers')->getStructure();
        $this->anotherTable = $db->getTable('anothers')->getStructure();
    }

    public function testCreatesForeignKeys()
    {
        $this->assertEquals(
                [
                        new ForeignKey(
                                'fk_one_anothers_one_id_ones',
                                ['one_id'],
                                'ones',
                                ['id'],
                                ForeignKeyMode::CASCADE,
                                ForeignKeyMode::CASCADE
                        ),
                        new ForeignKey(
                                'fk_one_anothers_another_id_anothers',
                                ['another_id'],
                                'anothers',
                                array('id'),
                                ForeignKeyMode::CASCADE,
                                ForeignKeyMode::CASCADE
                        ),
                ],
                array_values($this->joinTable->getForeignKeys())
        );
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
        $another1 = new AnotherEntity();
        $another2 = new AnotherEntity();
        $another3 = new AnotherEntity();
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
        ];;

        $this->repo->saveAll($entities);

        $this->assertDatabaseDataSameAs([
                'ones'         => [
                        ['id' => 1],
                        ['id' => 2],
                        ['id' => 3],
                ],
                'anothers'     => [
                        ['id' => 1],
                        ['id' => 2],
                        ['id' => 3],
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
                        ['id' => 1],
                        ['id' => 2],
                        ['id' => 3],
                ],
                'one_anothers' => [
                        ['one_id' => 1, 'another_id' => 1],
                        ['one_id' => 1, 'another_id' => 2],
                        ['one_id' => 1, 'another_id' => 3],
                ],
        ]);

        $entity = new OneEntity(1, [
                new AnotherEntity(),
                new AnotherEntity(),
                new AnotherEntity(2),
        ]);

        $this->repo->save($entity);

        $this->assertDatabaseDataSameAs([
                'ones'         => [
                        ['id' => 1],
                ],
                'anothers'     => [
                        ['id' => 1],
                        ['id' => 2],
                        ['id' => 3],
                        ['id' => 4],
                        ['id' => 5],
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

    public function testLoadWithDuplicates()
    {
        $this->db->setData([
                'ones'         => [
                        ['id' => 1]
                ],
                'anothers'     => [
                        ['id' => 1],
                ],
                'one_anothers' => [
                        ['one_id' => 1, 'another_id' => 1],
                        ['one_id' => 1, 'another_id' => 1],
                ],
        ]);

        /** @var OneEntity $actual */
        $actual = $this->repo->get(1);
        $this->assertSame(1, $actual->getId());
        $this->assertCount(2, $actual->others);
        $this->assertInstanceOf(AnotherEntity::class, $actual->others[0]);
        $this->assertInstanceOf(AnotherEntity::class, $actual->others[1]);
        $this->assertSame(1, $actual->others[0]->getId());
        $this->assertSame(1, $actual->others[0]->getId());
        $this->assertSame($actual, $actual->others[0]->ones[0]);
        $this->assertSame($actual->others[1], $actual->others[0]);

        $this->assertExecutedQueryTypes([
                'Select one entity'                        => Select::class,
                'Select related entities'                  => Select::class,
                'Select related entities (will be cached)' => Select::class,
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
                        ['id' => 1],
                        ['id' => 2],
                        ['id' => 3],
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

        // Expected layout
        $another1 = new AnotherEntity(1);
        $another2 = new AnotherEntity(2);
        $another3 = new AnotherEntity(3);
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
        $this->assertCount(3, $actual);
        $this->assertSame(1, $actual[0]->getId());
        $this->assertCount(3, $actual[0]->others);
        $this->assertSame(1, $actual[0]->others[0]->getId());
        $this->assertSame(2, $actual[0]->others[1]->getId());
        $this->assertSame(3, $actual[0]->others[2]->getId());
        $this->assertContains($actual[0], $actual[0]->others[0]->ones);

        $this->assertSame(2, $actual[1]->getId());
        $this->assertCount(2, $actual[1]->others);
        $this->assertSame(1, $actual[1]->others[0]->getId());
        $this->assertSame(3, $actual[1]->others[1]->getId());
        $this->assertContains($actual[1], $actual[1]->others[0]->ones);

        $this->assertSame(3, $actual[2]->getId());
        $this->assertCount(2, $actual[2]->others);
        $this->assertSame(2, $actual[2]->others[0]->getId());
        $this->assertSame(3, $actual[2]->others[1]->getId());
        $this->assertContains($actual[2], $actual[2]->others[0]->ones);

        $this->assertSame($actual[0]->others[0], $actual[1]->others[0]);

        $this->assertExecutedQueryTypes([
                'Load parent entities' => Select::class,
                'Load child entities'  => Select::class,
                'Load related entities'  => Select::class,
        ]);

        $this->assertExecutedQueryNumber(2, Select::from($this->anotherTable)
                ->addRawColumn('id')
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
                        ['id' => 1],
                        ['id' => 2],
                        ['id' => 3],
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
                        ['id' => 1],
                        ['id' => 2],
                        ['id' => 3],
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
<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Domains;

use Dms\Core\Persistence\Db\Mapping\IOrm;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Persistence\Db\Query\Select;
use Dms\Core\Persistence\Db\Schema\Column;
use Dms\Core\Persistence\Db\Schema\PrimaryKeyBuilder;
use Dms\Core\Persistence\Db\Schema\Type\Enum;
use Dms\Core\Persistence\Db\Schema\Type\Integer;
use Dms\Core\Persistence\Db\Schema\Type\Varchar;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Sport\Bowler;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Sport\Cricketer;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Sport\Footballer;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Sport\Mapper\SingleTableInheritancePlayerMapper;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Sport\Player;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\DbIntegrationTest;
use Dms\Core\Tests\Persistence\Db\Mock\MockDatabase;
use Dms\Core\Tests\Persistence\Db\Mock\MockTable;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PlayerSingleTableInheritanceTest extends DbIntegrationTest
{
    /**
     * @var MockTable
     */
    protected $playersTable;

    /**
     * @return IOrm
     */
    protected function loadOrm()
    {
        return SingleTableInheritancePlayerMapper::orm();
    }

    /**
     * @inheritDoc
     */
    protected function mapperAndRepoType()
    {
        return Player::class;
    }

    /**
     * @inheritDoc
     */
    protected function buildDatabase(MockDatabase $db, IOrm $orm)
    {
        parent::buildDatabase($db, $orm);

        $this->playersTable = $db->getTable('players');
    }

    public function testStructure()
    {
        $this->assertEquals([
                'id'              => PrimaryKeyBuilder::incrementingInt('id'),
                'type'            => new Column('type', new Enum(['footballer', 'cricketer', 'bowler'])),
                'name'            => new Column('name', new Varchar(255)),
                'club'            => new Column('club', (new Varchar(255))->nullable()),
                'batting_average' => new Column('batting_average', Integer::normal()->nullable()),
                'bowling_average' => new Column('bowling_average', Integer::normal()->nullable()),
        ], $this->playersTable->getStructure()->getColumns());
    }

    ////////////////
    // Persisting //
    ////////////////


    public function testPersist()
    {
        $this->repo->saveAll([
                new Bowler(null, 'Joe', 14, 3),
                new Cricketer(null, 'Jack', 10),
                new Footballer(null, 'John', 'Melbourne City'),
        ]);

        $this->assertDatabaseDataSameAs([
                'players' => [
                        [
                                'id'              => 1,
                                'type'            => 'bowler',
                                'name'            => 'Joe',
                                'club'            => null,
                                'batting_average' => 14,
                                'bowling_average' => 3
                        ],
                        [
                                'id'              => 2,
                                'type'            => 'cricketer',
                                'name'            => 'Jack',
                                'club'            => null,
                                'batting_average' => 10,
                                'bowling_average' => null
                        ],
                        [
                                'id'              => 3,
                                'type'            => 'footballer',
                                'name'            => 'John',
                                'club'            => 'Melbourne City',
                                'batting_average' => null,
                                'bowling_average' => null
                        ],
                ]
        ]);
    }


    /////////////
    // Loading //
    /////////////

    public function testLoadAll()
    {
        $this->setDataInDb([
                'players' => [
                        [
                                'id'              => 1,
                                'type'            => 'bowler',
                                'name'            => 'Joe',
                                'club'            => null,
                                'batting_average' => 14,
                                'bowling_average' => 3
                        ],
                        [
                                'id'              => 2,
                                'type'            => 'cricketer',
                                'name'            => 'Jack',
                                'club'            => null,
                                'batting_average' => 10,
                                'bowling_average' => null
                        ],
                        [
                                'id'              => 3,
                                'type'            => 'footballer',
                                'name'            => 'John',
                                'club'            => 'Melbourne City',
                                'batting_average' => null,
                                'bowling_average' => null
                        ],
                ]
        ]);

        $this->assertEquals([
                new Bowler(1, 'Joe', 14, 3),
                new Cricketer(2, 'Jack', 10),
                new Footballer(3, 'John', 'Melbourne City')
        ], $this->repo->getAll());
    }

    //////////////
    // Removing //
    //////////////

    public function testRemove()
    {
        $this->setDataInDb([
                'players' => [
                        [
                                'id'              => 1,
                                'type'            => 'bowler',
                                'name'            => 'Joe',
                                'club'            => null,
                                'batting_average' => 14,
                                'bowling_average' => 3
                        ],
                        [
                                'id'              => 2,
                                'type'            => 'cricketer',
                                'name'            => 'Jack',
                                'club'            => null,
                                'batting_average' => 10,
                                'bowling_average' => null
                        ],
                        [
                                'id'              => 3,
                                'type'            => 'footballer',
                                'name'            => 'John',
                                'club'            => 'Melbourne City',
                                'batting_average' => null,
                                'bowling_average' => null
                        ],
                ]
        ]);

        $this->repo->removeAllById([1, 3]);

        $this->assertDatabaseDataSameAs([
                'players' => [
                        [
                                'id'              => 2,
                                'type'            => 'cricketer',
                                'name'            => 'Jack',
                                'club'            => null,
                                'batting_average' => 10,
                                'bowling_average' => null
                        ],
                ]
        ]);
    }

    //////////////
    // Criteria //
    //////////////

    public function testLoadWithSubclassProperty()
    {
        $this->setDataInDb([
            'players'     => [
                [
                    'id'              => 1,
                    'type'            => 'bowler',
                    'name'            => 'Joe',
                    'club'            => null,
                    'batting_average' => 14,
                    'bowling_average' => 3
                ],
                [
                    'id'              => 2,
                    'type'            => 'bowler',
                    'name'            => 'John',
                    'club'            => null,
                    'batting_average' => 2,
                    'bowling_average' => 5
                ],
                [
                    'id'              => 3,
                    'type'            => 'footballer',
                    'name'            => 'John',
                    'club'            => 'Melbourne City',
                    'batting_average' => null,
                    'bowling_average' => null
                ],
            ],
        ]);

        $bowlers = $this->repo->matching(
            $this->repo->criteria()
                ->whereInstanceOf(Bowler::class)
                ->where('bowlingAverage', '>', 4)
        );

        $this->assertEquals(
            [$this->repo->get(2)],
            $bowlers
        );

        $this->assertExecutedQueryNumber(
            1,
            Select::from($this->getSchemaTable('players'))
                ->addRawColumn('id')
                ->addRawColumn('type')
                ->addRawColumn('name')
                ->addRawColumn('batting_average')
                ->addRawColumn('bowling_average')
                ->where(Expr::equal(
                    Expr::tableColumn($this->getSchemaTable('players'), 'type'),
                    Expr::param($this->getSchemaTable('players')->getColumn('type')->getType(), 'bowler')
                ))
                ->where(Expr::greaterThan(
                    Expr::tableColumn($this->getSchemaTable('players'), 'bowling_average'),
                    Expr::param(null, 4)
                ))
        );
    }
}
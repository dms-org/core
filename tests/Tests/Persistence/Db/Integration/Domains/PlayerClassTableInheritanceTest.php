<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Domains;

use Dms\Core\Persistence\Db\Mapping\IOrm;
use Dms\Core\Persistence\Db\Schema\Column;
use Dms\Core\Persistence\Db\Schema\PrimaryKeyBuilder;
use Dms\Core\Persistence\Db\Schema\Type\Integer;
use Dms\Core\Persistence\Db\Schema\Type\Varchar;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Sport\Bowler;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Sport\Cricketer;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Sport\Footballer;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Sport\Mapper\ClassTableInheritancePlayerMapper;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Sport\Player;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\DbIntegrationTest;
use Dms\Core\Tests\Persistence\Db\Mock\MockDatabase;
use Dms\Core\Tests\Persistence\Db\Mock\MockTable;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PlayerClassTableInheritanceTest extends DbIntegrationTest
{
    /**
     * @var MockTable
     */
    protected $playersTable;

    /**
     * @var MockTable
     */
    protected $footballersTable;

    /**
     * @var MockTable
     */
    protected $cricketersTable;

    /**
     * @var MockTable
     */
    protected $bowlersTable;

    /**
     * @return IOrm
     */
    protected function loadOrm()
    {
        return ClassTableInheritancePlayerMapper::orm();
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

        $this->playersTable     = $db->getTable('players');
        $this->footballersTable = $db->getTable('footballers');
        $this->cricketersTable  = $db->getTable('cricketers');
        $this->bowlersTable     = $db->getTable('bowlers');
    }

    public function testStructure()
    {
        $this->assertEquals([
                'id'   => PrimaryKeyBuilder::incrementingInt('id'),
                'name' => new Column('name', new Varchar(255)),
        ], $this->playersTable->getStructure()->getColumns());


        $this->assertEquals([
                'id'   => PrimaryKeyBuilder::incrementingInt('id'),
                'club' => new Column('club', new Varchar(255)),
        ], $this->footballersTable->getStructure()->getColumns());

        $this->assertEquals([
                'id'              => PrimaryKeyBuilder::incrementingInt('id'),
                'batting_average' => new Column('batting_average', Integer::normal()),
        ], $this->cricketersTable->getStructure()->getColumns());

        $this->assertEquals([
                'id'              => PrimaryKeyBuilder::incrementingInt('id'),
                'bowling_average' => new Column('bowling_average', Integer::normal()),
        ], $this->bowlersTable->getStructure()->getColumns());
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
                'players'     => [
                        ['id' => 1, 'name' => 'Joe'],
                        ['id' => 2, 'name' => 'Jack'],
                        ['id' => 3, 'name' => 'John'],
                ],
                'footballers' => [
                        ['id' => 3, 'club' => 'Melbourne City'],
                ],
                'cricketers'  => [
                        ['id' => 1, 'batting_average' => 14],
                        ['id' => 2, 'batting_average' => 10],
                ],
                'bowlers'     => [
                        ['id' => 1, 'bowling_average' => 3],
                ],
        ]);
    }


    /////////////
    // Loading //
    /////////////

    public function testLoadAll()
    {
        $this->setDataInDb([
                'players'     => [
                        ['id' => 1, 'name' => 'Joe'],
                        ['id' => 2, 'name' => 'Jack'],
                        ['id' => 3, 'name' => 'John'],
                ],
                'footballers' => [
                        ['id' => 3, 'club' => 'Melbourne City'],
                ],
                'cricketers'  => [
                        ['id' => 1, 'batting_average' => 14],
                        ['id' => 2, 'batting_average' => 10],
                ],
                'bowlers'     => [
                        ['id' => 1, 'bowling_average' => 3],
                ],
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
                'players'     => [
                        ['id' => 1, 'name' => 'Joe'],
                        ['id' => 2, 'name' => 'Jack'],
                        ['id' => 3, 'name' => 'John'],
                ],
                'footballers' => [
                        ['id' => 3, 'club' => 'Melbourne City'],
                ],
                'cricketers'  => [
                        ['id' => 1, 'batting_average' => 14],
                        ['id' => 2, 'batting_average' => 10],
                ],
                'bowlers'     => [
                        ['id' => 1, 'bowling_average' => 3],
                ],
        ]);

        $this->repo->removeAllById([1, 3]);

        $this->assertDatabaseDataSameAs([
                'players'     => [
                        ['id' => 2, 'name' => 'Jack'],
                ],
                'footballers' => [
                ],
                'cricketers'  => [
                        ['id' => 2, 'batting_average' => 10],
                ],
                'bowlers'     => [
                ],
        ]);
    }
}
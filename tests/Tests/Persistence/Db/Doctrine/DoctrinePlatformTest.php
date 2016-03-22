<?php

namespace Dms\Core\Tests\Persistence\Db\Doctrine;

use Dms\Core\Persistence\Db\Doctrine\DoctrinePlatform;
use Dms\Core\Persistence\Db\Query\Clause\Join;
use Dms\Core\Persistence\Db\Query\Delete;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Persistence\Db\Query\Select;
use Dms\Core\Persistence\Db\Query\Update;
use Dms\Core\Persistence\Db\Schema\Column;
use Dms\Core\Persistence\Db\Schema\PrimaryKeyBuilder;
use Dms\Core\Persistence\Db\Schema\Table;
use Dms\Core\Persistence\Db\Schema\Type\Text;
use Dms\Core\Persistence\Db\Schema\Type\Varchar;
use Dms\Core\Tests\Persistence\Db\Doctrine\Mocks\ConnectionMock;
use Dms\Core\Tests\Persistence\Db\Doctrine\Mocks\DriverMock;
use Doctrine\DBAL\Platforms\MySqlPlatform;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DoctrinePlatformTest extends DoctrineTestBase
{
    /**
     * @var DoctrinePlatform
     */
    protected $platform;

    public function setUp()
    {
        // Use mysql as a test platform
        $platform = new MySqlPlatform();

        $driver = new DriverMock();
        $driver->setDatabasePlatform($platform);
        $this->platform = new DoctrinePlatform(new ConnectionMock([], $driver));
    }

    /**
     * @return Table
     */
    protected function mockTable()
    {
        return new Table('foo', [
                PrimaryKeyBuilder::incrementingInt('id'),
                new Column('data', new Varchar(255)),
        ]);
    }

    public function testQuoteIdentifier()
    {
        $this->assertSame('`foo`',  $this->platform->quoteIdentifier('foo'));
        $this->assertSame('`foo.bar`',  $this->platform->quoteIdentifier('foo.bar'));
        $this->assertSame('````',  $this->platform->quoteIdentifier('`'));
        $this->assertSame('`abc``123`',  $this->platform->quoteIdentifier('abc`123'));
    }

    public function testPreparedInsert()
    {
        $sql = $this->platform->compilePreparedInsert($this->mockTable());

        $this->assertSqlSame('INSERT INTO `foo` (`id`, `data`) VALUES (:id, :data)', $sql);
    }

    public function testPreparedUpdate()
    {
        $sql = $this->platform->compilePreparedUpdate($this->mockTable(), ['data'], ['id' => 'some_param_id']);

        $this->assertSqlSame('UPDATE `foo` SET `data` = :data WHERE `id` = :some_param_id', $sql);
    }

    public function testSimpleDelete()
    {
        $query = $this->platform->compileDelete(
                Delete::from($this->mockTable())
        );

        $this->assertSqlSame('DELETE FROM `foo`', $query->getSql());
        $this->assertSame([], $query->getParameters());
    }

    public function testComplexDelete()
    {
        $table = $this->mockTable();
        $query = $this->platform->compileDelete(
                Delete::from($table)
                        ->where(Expr::lessThan(Expr::tableColumn($table, 'id'), Expr::idParam(15)))
                        ->orderByDesc(Expr::tableColumn($table, 'id'))
                        ->offset(10)
                        ->limit(100)
        );

        $this->assertSqlSame(<<<'SQL'
DELETE FROM `foo`
WHERE `id` IN (SELECT * FROM (
  SELECT
    `foo`.`id` AS `id`
  FROM `foo`
  WHERE `foo`.`id` < ?
  ORDER BY `foo`.`id` DESC
  LIMIT 100
  OFFSET 10
) `foo`)
SQL
                , $query->getSql());
        $this->assertSame([0 => 15], $query->getParameters());
    }

    public function testSimpleUpdate()
    {
        $query = $this->platform->compileUpdate(
                Update::from($this->mockTable())
                        ->set('data', Expr::param(new Varchar(500), 'foo'))
        );

        $this->assertSqlSame('UPDATE `foo` SET `data` = ?', $query->getSql());
        $this->assertSame([1 => 'foo'], $query->getParameters());
    }

    public function testComplexUpdate()
    {
        $table = $this->mockTable();
        $query = $this->platform->compileUpdate(
                Update::from($table)
                        ->set('id', Expr::tableColumn($table, 'id'))
                        ->set('data', Expr::count())
                        ->join(Join::inner($table, 'bar',
                                [Expr::equal(Expr::tableColumn($table, 'id'), Expr::column('bar', $table->findColumn('id')))]))
                        ->where(Expr::lessThan(Expr::tableColumn($table, 'id'), Expr::idParam(15)))
                        ->orderByDesc(Expr::tableColumn($table, 'id'))
                        ->offset(10)
                        ->limit(100)
        );

        $this->assertSqlSame(<<<'SQL'
UPDATE `foo` SET
  `id` = `foo`.`id`,
  `data` = COUNT(*)
WHERE `id` IN (SELECT * FROM (
  SELECT
    `foo`.`id` AS `id`
  FROM `foo`
  INNER JOIN `foo` `bar` ON `foo`.`id` = `bar`.`id`
  WHERE `foo`.`id` < ?
  ORDER BY `foo`.`id` DESC
  LIMIT 100
  OFFSET 10
) `foo`)
SQL
                , $query->getSql());
        $this->assertSame([0 => 15], $query->getParameters());
    }

    public function testCompiledUpdateWithMultipleWhereConditions()
    {
        $table = $this->mockTable();
        $query = $this->platform->compilePreparedUpdate($table, ['id', 'data'], ['id' => 'lock_id', 'data' => 'lock_data']);

        $this->assertSqlSame(<<<'SQL'
UPDATE `foo` SET
  `id` = :id,
  `data` = :data
WHERE (`id` = :lock_id) AND (`data` = :lock_data)
SQL
                , $query);

    }

    public function testSimpleSelect()
    {
        $query = $this->platform->compileSelect(
                Select::allFrom($this->mockTable())
        );

        $this->assertSqlSame('SELECT `foo`.`id` AS `id`, `foo`.`data` AS `data` FROM `foo`', $query->getSql());
        $this->assertSame([], $query->getParameters());
    }

    public function testComplexSelect()
    {
        $table = $this->mockTable();
        $query = $this->platform->compileSelect(
                Select::allFrom($table)
                        ->join(Join::left($table, 't2', []))
                        ->where(Expr::lessThan(Expr::tableColumn($table, 'id'), Expr::idParam(15)))
                        ->where(Expr::strContainsCaseInsensitive(Expr::tableColumn($table, 'data'), Expr::param(Text::long(), 'abc')))
                        ->addGroupBy(Expr::tableColumn($table, 'data'))
                        ->addHaving(Expr::greaterThan(Expr::column('t2', $table->findColumn('id')), Expr::idParam(10)))
                        ->orderByDesc(Expr::column('t2', $table->findColumn('id')))
                        ->offset(10)
                        ->limit(100)
        );

        $this->assertSqlSame(<<<'SQL'
SELECT
  `foo`.`id` AS `id`,
  `foo`.`data` AS `data`
FROM `foo`
LEFT JOIN `foo` `t2` ON 1=1
WHERE (`foo`.`id` < ?) AND (LOCATE(UPPER(?), UPPER(`foo`.`data`)) > 0)
GROUP BY `foo`.`data`
HAVING `t2`.`id` > ?
ORDER BY `t2`.`id` DESC
LIMIT 100 OFFSET 10
SQL
                , $query->getSql());
        $this->assertSame([1 => 15, 2 => 'abc', 3 => 10], $query->getParameters());
    }

}
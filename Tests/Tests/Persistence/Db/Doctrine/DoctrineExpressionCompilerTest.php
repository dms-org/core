<?php

namespace Dms\Core\Tests\Persistence\Db\Doctrine;

use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Query\QueryBuilder;
use Dms\Core\Persistence\Db\Doctrine\DoctrineExpressionCompiler;
use Dms\Core\Persistence\Db\Doctrine\DoctrinePlatform;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Persistence\Db\Query\Select;
use Dms\Core\Persistence\Db\Schema\Column;
use Dms\Core\Persistence\Db\Schema\Table;
use Dms\Core\Persistence\Db\Schema\Type\Date;
use Dms\Core\Persistence\Db\Schema\Type\DateTime;
use Dms\Core\Persistence\Db\Schema\Type\Integer;
use Dms\Core\Persistence\Db\Schema\Type\Text;
use Dms\Core\Persistence\Db\Schema\Type\Time;
use Dms\Core\Tests\Persistence\Db\Doctrine\Mocks\ConnectionMock;
use Dms\Core\Tests\Persistence\Db\Doctrine\Mocks\DriverMock;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DoctrineExpressionCompilerTest extends DoctrineTestBase
{
    /**
     * @var ConnectionMock
     */
    protected $connection;

    /**
     * @var DoctrineExpressionCompiler
     */
    protected $compiler;

    public function setUp()
    {
        // Use mysql as a test platform
        $platform = new MySqlPlatform();

        $driver = new DriverMock();
        $driver->setDatabasePlatform($platform);
        $this->connection = new ConnectionMock([], $driver);
        $platform         = new DoctrinePlatform($this->connection);
        $this->compiler   = new DoctrineExpressionCompiler($platform);
    }

    public function expressions()
    {
        $testColumn = Expr::column('table', new Column('column', Text::long()));

        return [
            //
            // Column
            [$testColumn, '`table`.`column`'],
            //
            // Aggregates
            [Expr::count(), 'COUNT(*)'],
            [Expr::max($testColumn), 'MAX(`table`.`column`)'],
            [Expr::min($testColumn), 'MIN(`table`.`column`)'],
            [Expr::avg($testColumn), 'AVG(`table`.`column`)'],
            [Expr::sum($testColumn), 'SUM(`table`.`column`)'],
            //
            // Parameter
            [Expr::param(Text::long(), 'foobar'), '?', [1 => 'foobar']],
            [Expr::param(Text::long()->nullable(), null), '?', [1 => null]],
            [Expr::param(Integer::normal(), 123), '?', [1 => 123]],
            [Expr::param(Integer::normal()->nullable(), null), '?', [1 => null]],
            [Expr::param(new Time(), new \DateTime('10:00 AM')), '?', [1 => '10:00:00']],
            [Expr::param(new Date(), new \DateTime('2000-03-05')), '?', [1 => '2000-03-05']],
            [Expr::param(new DateTime(), new \DateTime('2000-03-05 10:04:35')), '?', [1 => '2000-03-05 10:04:35']],
            //
            // Binary ops
            [Expr::equal($testColumn, $testColumn), '`table`.`column` = `table`.`column`'],
            [Expr::notEqual($testColumn, $testColumn), '`table`.`column` <> `table`.`column`'],
            [Expr::lessThan($testColumn, $testColumn), '`table`.`column` < `table`.`column`'],
            [Expr::lessThanOrEqual($testColumn, $testColumn), '`table`.`column` <= `table`.`column`'],
            [Expr::greaterThan($testColumn, $testColumn), '`table`.`column` > `table`.`column`'],
            [Expr::greaterThanOrEqual($testColumn, $testColumn), '`table`.`column` >= `table`.`column`'],
            [
                    Expr::strContains($testColumn, Expr::param(Text::long(), 'foo')),
                    'LOCATE(?, `table`.`column`) > 0',
                    [1 => 'foo']
            ],
            [
                    Expr::strContainsCaseInsensitive($testColumn, Expr::param(Text::long(), 'bar')),
                    'LOCATE(UPPER(?), UPPER(`table`.`column`)) > 0',
                    [1 => 'bar']
            ],
            [Expr::and_($testColumn, $testColumn), '(`table`.`column`) AND (`table`.`column`)'],
            [Expr::or_($testColumn, $testColumn), '(`table`.`column`) OR (`table`.`column`)'],
            [
                    Expr::in($testColumn, Expr::tuple([Expr::idParam(1), Expr::idParam(2), Expr::idParam(3)])),
                    '`table`.`column` IN (?, ?, ?)',
                    [1 => 1, 2 => 2, 3 => 3]
            ],
            [
                    Expr::notIn($testColumn, Expr::tuple([Expr::idParam(3), Expr::idParam(2), Expr::idParam(1)])),
                    '`table`.`column` NOT IN (?, ?, ?)',
                    [1 => 3, 2 => 2, 3 => 1]
            ],
            //
            // Unary ops
            [Expr::not($testColumn), 'NOT(`table`.`column`)'],
            [Expr::isNull($testColumn), '`table`.`column` IS NULL'],
            [Expr::isNotNull($testColumn), '`table`.`column` IS NOT NULL'],
            // Sub select
            [Expr::subSelect(
                    (new Select(new Table('table', [$testColumn->getColumn()])))
                            ->addRawColumn($testColumn->getName())
                            ->where($testColumn)
            ), '(SELECT `table`.`column` AS `column` FROM `table` WHERE `table`.`column`) AS `__sub_select_0`'],
        ];
    }

    /**
     * @dataProvider expressions
     */
    public function testCompilesExpressionCorrectly(Expr $expr, $expectedSql, array $expectedParams = [])
    {
        $this->assertCompiles($expectedSql, $expectedParams, $expr);
    }

    private function assertCompiles($expectedSql, array $expectedParameters, Expr $expression)
    {
        $queryBuilder = new QueryBuilder($this->connection);
        $this->assertSame($expectedSql, $this->compiler->compileExpression($queryBuilder, $expression));
        $this->assertSame($expectedParameters, $queryBuilder->getParameters());
    }
}
<?php

namespace Dms\Core\Tests\Persistence\Db\Doctrine\Resequence;

use Dms\Core\Persistence\Db\Schema\PrimaryKeyBuilder;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Dms\Core\Persistence\Db\Doctrine\DoctrinePlatform;
use Dms\Core\Persistence\Db\Doctrine\IResequenceCompiler;
use Dms\Core\Persistence\Db\Doctrine\Resequence\DefaultResequenceCompiler;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Persistence\Db\Query\ResequenceOrderIndexColumn;
use Dms\Core\Persistence\Db\Schema\Column;
use Dms\Core\Persistence\Db\Schema\Table;
use Dms\Core\Persistence\Db\Schema\Type\Integer;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DefaultResequenceCompilerTest extends ResequenceCompilerTestBase
{
    /**
     * @return AbstractPlatform
     */
    protected function buildDoctrinePlatform()
    {
        return new SqlitePlatform();
    }

    /**
     * @param DoctrinePlatform $platform
     *
     * @return IResequenceCompiler
     */
    protected function buildCompiler(DoctrinePlatform $platform)
    {
        return new DefaultResequenceCompiler($platform->getExpressionCompiler());
    }

    public function testCompilesCorrectly()
    {
        $query = new ResequenceOrderIndexColumn(
                new Table('test', [
                        PrimaryKeyBuilder::incrementingInt('id'),
                        new Column('order_index', Integer::normal()),
                ]),
                'order_index',
                null,
                Expr::greaterThan(Expr::idParam(1), Expr::idParam(0))
        );

        $compiled = $this->compiler->compileResequenceQuery(
                $this->doctrineConnection->createQueryBuilder(),
                $query
        );

        $this->assertSqlSame(<<<'SQL'
UPDATE "test"
SET "order_index" = (
  SELECT COUNT(*) AS row_number
  FROM "test" "test__inner"
  WHERE "test__inner"."order_index" <= "test"."order_index"
)
WHERE ? > ?
SQL
                , $compiled->getSql());

        $this->assertSqlSame([1 => 1, 2 => 0], $compiled->getParameters());
    }

    public function testCompilesCorrectlyWithGroupingColumn()
    {
        $query = new ResequenceOrderIndexColumn(
                new Table('test', [
                        PrimaryKeyBuilder::incrementingInt('id'),
                        new Column('order_index', Integer::normal()),
                        new Column('group', Integer::normal()),
                ]),
                'order_index',
                'group',
                Expr::greaterThan(Expr::idParam(1), Expr::idParam(0))
        );

        $compiled = $this->compiler->compileResequenceQuery(
                $this->doctrineConnection->createQueryBuilder(),
                $query
        );

        $this->assertSqlSame(<<<'SQL'
UPDATE "test"
SET "order_index" = (
  SELECT COUNT(*) AS row_number
  FROM "test" "test__inner"
  WHERE ("test__inner"."order_index" <= "test"."order_index")
  AND ("test__inner"."group" = "test"."group")
)
WHERE ? > ?
SQL
                , $compiled->getSql());

        $this->assertSqlSame([1 => 1, 2 => 0], $compiled->getParameters());
    }
}
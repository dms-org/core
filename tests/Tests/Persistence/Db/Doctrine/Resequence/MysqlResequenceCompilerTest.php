<?php

namespace Dms\Core\Tests\Persistence\Db\Doctrine\Resequence;

use Dms\Core\Persistence\Db\Doctrine\DoctrinePlatform;
use Dms\Core\Persistence\Db\Doctrine\IResequenceCompiler;
use Dms\Core\Persistence\Db\Doctrine\Resequence\MysqlResequenceCompiler;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Persistence\Db\Query\ResequenceOrderIndexColumn;
use Dms\Core\Persistence\Db\Schema\Column;
use Dms\Core\Persistence\Db\Schema\PrimaryKeyBuilder;
use Dms\Core\Persistence\Db\Schema\Table;
use Dms\Core\Persistence\Db\Schema\Type\Integer;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySqlPlatform;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MysqlResequenceCompilerTest extends ResequenceCompilerTestBase
{
    /**
     * @return AbstractPlatform
     */
    protected function buildDoctrinePlatform()
    {
        return new MySqlPlatform();
    }

    /**
     * @param DoctrinePlatform $platform
     *
     * @return IResequenceCompiler
     */
    protected function buildCompiler(DoctrinePlatform $platform)
    {
        return new MysqlResequenceCompiler($platform->getExpressionCompiler());
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
UPDATE `test`
SET
`order_index` =  (@__rownum := @__rownum + 1) 
WHERE ? > ?
ORDER BY `order_index`, @__rownum := 0
SQL
            , $compiled->getSql());

        $this->assertSqlSame([1 => 1, 2 => 0], $compiled->getParameters());
    }

    public function testCompilesCorrectlyWithGroup()
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
UPDATE `test`
SET
`order_index` =  (@__rownum := IF(`group` = @__previous_group, @__rownum, 0) + 1),
`id` = IF((@__previous_group:=`group`) IS NOT NULL, `id`, `id`)
WHERE ? > ?
ORDER BY `group`, `order_index`, @__previous_group := NULL, @__rownum := 0
SQL
            , $compiled->getSql());

        $this->assertSqlSame([1 => 1, 2 => 0], $compiled->getParameters());
    }
}
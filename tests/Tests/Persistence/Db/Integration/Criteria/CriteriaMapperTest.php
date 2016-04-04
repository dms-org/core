<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Criteria;

use Dms\Core\Exception\TypeMismatchException;
use Dms\Core\Model\Criteria\Criteria;
use Dms\Core\Model\Criteria\SpecificationDefinition;
use Dms\Core\Persistence\Db\Criteria\CriteriaMapper;
use Dms\Core\Persistence\Db\Mapping\CustomOrm;
use Dms\Core\Persistence\Db\Query\Clause\Ordering;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Tests\Persistence\Db\Fixtures\MockEntity;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\Types\TypesEntity;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\Types\TypesMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CriteriaMapperTest extends CriteriaMapperTestBase
{
    protected function buildMapper()
    {
        return new CriteriaMapper(new TypesMapper(CustomOrm::from([])));
    }

    public function testInvalidCriteria()
    {
        $this->setExpectedException(TypeMismatchException::class);
        $criteria = new Criteria(MockEntity::definition());

        $this->mapper->mapCriteriaToSelect($criteria);
    }

    public function testEmptyCriteria()
    {
        $criteria = $this->mapper->newCriteria();

        $this->assertMappedSelect($criteria, $this->selectAllColumns());
    }

    public function testLimitCriteria()
    {
        $criteria = $this->mapper->newCriteria()->limit(5);

        $this->assertMappedSelect($criteria, $this->selectAllColumns()->limit(5));
    }

    public function testSkipCriteria()
    {
        $criteria = $this->mapper->newCriteria()->skip(2);

        $this->assertMappedSelect($criteria, $this->selectAllColumns()->offset(2));
    }

    public function testOrderingCriteria()
    {
        $criteria = $this->mapper->newCriteria()
            ->orderByAsc('string')
            ->orderByDesc('int');

        $this->assertMappedSelect($criteria,
            $this->selectAllColumns()
                ->orderBy(new Ordering($this->column('string'), Ordering::ASC))
                ->orderBy(new Ordering($this->column('int'), Ordering::DESC))
        );
    }

    public function testConditionCriteria()
    {
        $criteria = $this->mapper->newCriteria()
            ->where('string', '=', 'foo')
            ->where('null', '=', null)
            ->where('null', '!=', null)
            ->where('int', '>', 5)
            ->where('float', '>=', -2.0)
            ->where('date', '<', new \DateTimeImmutable('2000-01-01'))
            ->where('time', '<=', new \DateTimeImmutable('15:03'))
            ->whereIn('string', ['foo', 'bar'])
            ->whereNotIn('float', [1.0, -20.0, 35.5])
            ->whereStringContains('string', 'foo')
            ->whereStringContainsCaseInsensitive('string', 'bar');

        $this->assertMappedSelect($criteria,
            $this->selectAllColumns()
                ->where(Expr::equal($this->column('string'), Expr::param(null, 'foo')))
                ->where(Expr::isNull($this->column('null')))
                ->where(Expr::isNotNull($this->column('null')))
                ->where(Expr::greaterThan($this->column('int'), Expr::param(null, 5)))
                ->where(Expr::greaterThanOrEqual($this->column('float'), Expr::param(null, -2.0)))
                ->where(Expr::lessThan($this->column('date'),
                    Expr::param(null, new \DateTimeImmutable('2000-01-01'))))
                ->where(Expr::lessThanOrEqual($this->column('time'),
                    Expr::param(null, new \DateTimeImmutable('15:03'))))
                ->where(Expr::in($this->column('string'), Expr::tuple([
                    Expr::param(null, 'foo'),
                    Expr::param(null, 'bar'),
                ])))
                ->where(Expr::notIn($this->column('float'), Expr::tuple([
                    Expr::param(null, 1.0),
                    Expr::param(null, -20.0),
                    Expr::param(null, 35.5),
                ])))
                ->where(Expr::strContains($this->column('string'), Expr::param(null, 'foo')))
                ->where(Expr::strContainsCaseInsensitive($this->column('string'), Expr::param(null, 'bar')))
        );
    }

    public function testWhereSelfEqualsUsesIdComparison()
    {
        $criteria = $this->mapper->newCriteria()
            ->where('this', '=', new TypesEntity(3));

        $this->assertMappedSelect($criteria,
            $this->selectAllColumns()
                ->where(Expr::equal($this->column('id'), Expr::param(null, 3)))
        );
    }

    public function testWhereSelfInUsesIdComparison()
    {
        $criteria = $this->mapper->newCriteria()
            ->whereIn('this', [new TypesEntity(3), new TypesEntity(4)]);

        $this->assertMappedSelect($criteria,
            $this->selectAllColumns()
                ->where(Expr::or_(
                    Expr::equal($this->column('id'), Expr::param(null, 3)),
                    Expr::equal($this->column('id'), Expr::param(null, 4))
                ))
        );
    }

    public function testInstanceOfSameEntityDoesNothing()
    {
        $criteria = $this->mapper->newCriteria()
            ->whereInstanceOf(TypesEntity::class);

        $this->assertMappedSelect(
            $criteria,
            $this->selectAllColumns()
        );
    }

    public function testComplexConditionExpression()
    {
        $criteria = $this->mapper->newCriteria()
            ->where('string', '=', 'foo')
            ->whereAny(function (SpecificationDefinition $match) {
                $match->where('null', '!=', null);
                $match->where('float', '>=', -2.0);

                $match->whereAll(function (SpecificationDefinition $match) {
                    $match->where('int', '>', 5);
                    $match->where('time', '<=', new \DateTimeImmutable('15:03'));
                });

                $match->whereNot(function (SpecificationDefinition $match) {
                    $match->where('int', '=', 3);
                });
            });

        $this->assertMappedSelect($criteria,
            $this->selectAllColumns()
                ->where(Expr::equal($this->column('string'), Expr::param(null, 'foo')))
                ->where(Expr::compoundOr([
                    Expr::isNotNull($this->column('null')),
                    Expr::greaterThanOrEqual($this->column('float'), Expr::param(null, -2.0)),
                    Expr::compoundAnd([
                        Expr::greaterThan($this->column('int'), Expr::param(null, 5)),
                        Expr::lessThanOrEqual(
                            $this->column('time'),
                            Expr::param(null, new \DateTimeImmutable('15:03'))
                        ),
                    ]),
                    Expr::not(Expr::equal($this->column('int'), Expr::param(null, 3))),
                ]))
        );
    }
}
<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Criteria;

use Iddigital\Cms\Core\Persistence\Db\Criteria\CriteriaMapper;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\Expr;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObject\CurrencyEnum;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObject\EmbeddedMoneyObject;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObject\EntityWithValueObjectMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CriteriaMapperWithNullableValueObjectsTest extends CriteriaMapperTestBase
{
    protected function buildMapper()
    {
        return new CriteriaMapper(new EntityWithValueObjectMapper(EntityWithValueObjectMapper::orm()));
    }

    public function testWhereEquals()
    {
        $criteria = $this->mapper->newCriteria()
                ->where('nullableMoney', '=', new EmbeddedMoneyObject(200, CurrencyEnum::aud()));

        $this->assertMappedSelect($criteria,
                $this->selectAllColumns()
                        ->where(Expr::compoundAnd([
                                Expr::equal(
                                        $this->column('nullable_cents'),
                                        Expr::param($this->columnType('nullable_cents'), 200)
                                ),
                                Expr::equal(
                                        $this->column('nullable_currency'),
                                        Expr::param($this->columnType('nullable_currency'), 'AUD')
                                ),
                        ]))
        );
    }

    public function testWhereEqualsNull()
    {
        $criteria = $this->mapper->newCriteria()
                ->where('nullableMoney', '=', null);

        $this->assertMappedSelect($criteria,
                $this->selectAllColumns()
                        ->where(Expr::equal(
                                $this->column('has_nullable_money'),
                                Expr::param($this->columnType('has_nullable_money'), false)
                        ))
        );
    }

    public function testWhereNotEquals()
    {
        $criteria = $this->mapper->newCriteria()
                ->where('nullableMoney', '!=', new EmbeddedMoneyObject(0, CurrencyEnum::usd()));

        $this->assertMappedSelect($criteria,
                $this->selectAllColumns()
                        ->where(Expr::compoundAnd([
                                Expr::notEqual(
                                        $this->column('nullable_cents'),
                                        Expr::param($this->columnType('nullable_cents'), 0)
                                ),
                                Expr::notEqual(
                                        $this->column('nullable_currency'),
                                        Expr::param($this->columnType('nullable_currency'), 'USD')
                                ),
                        ]))
        );
    }

    public function testWhereNotEqualsNull()
    {
        $criteria = $this->mapper->newCriteria()
                ->where('nullableMoney', '!=', null);

        $this->assertMappedSelect($criteria,
                $this->selectAllColumns()
                        ->where(Expr::equal(
                                $this->column('has_nullable_money'),
                                Expr::param($this->columnType('has_nullable_money'), true)
                        ))
        );
    }
}
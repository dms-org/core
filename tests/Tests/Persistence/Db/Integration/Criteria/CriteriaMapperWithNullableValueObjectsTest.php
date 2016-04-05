<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Criteria;

use Dms\Core\Persistence\Db\Criteria\CriteriaMapper;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObject\CurrencyEnum;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObject\EmbeddedMoneyObject;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObject\EntityWithValueObjectMapper;

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
                        $this->column('nullable_currency'),
                        Expr::param(null, 'AUD')
                    ),
                    Expr::equal(
                        $this->column('nullable_cents'),
                        Expr::param(null, 200)
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
                ->where(Expr::compoundOr([
                    Expr::notEqual(
                        $this->column('nullable_currency'),
                        Expr::param(null, 'USD')
                    ),
                    Expr::notEqual(
                        $this->column('nullable_cents'),
                        Expr::param(null, 0)
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
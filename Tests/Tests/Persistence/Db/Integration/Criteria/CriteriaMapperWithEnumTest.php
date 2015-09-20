<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Criteria;

use Iddigital\Cms\Core\Persistence\Db\Criteria\CriteriaMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\CustomOrm;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\Expr;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\Enum\EntityWithEnumMapper;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\Enum\StatusEnum;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CriteriaMapperWithEnumTest extends CriteriaMapperTestBase
{

    protected function buildMapper()
    {
        return new CriteriaMapper(new EntityWithEnumMapper(CustomOrm::from([])));
    }

    public function testWhereEquals()
    {
        $criteria = $this->mapper->newCriteria()
                ->where('nullableStatus', '=', StatusEnum::active());

        $this->assertMappedSelect($criteria,
                $this->selectAllColumns()
                        ->where(Expr::equal($this->column('nullable_status'), Expr::param($this->columnType('nullable_status'), 'active')))
        );
    }

    public function testWhereEqualsNull()
    {
        $criteria = $this->mapper->newCriteria()
                ->where('nullableStatus', '=', null);

        $this->assertMappedSelect($criteria,
                $this->selectAllColumns()
                        ->where(Expr::isNull($this->column('nullable_status')))
        );
    }

    public function testWhereNotEquals()
    {
        $criteria = $this->mapper->newCriteria()
                ->where('nullableStatus', '!=', StatusEnum::inactive());

        $this->assertMappedSelect($criteria,
                $this->selectAllColumns()
                        ->where(Expr::notEqual($this->column('nullable_status'),
                                Expr::param($this->columnType('nullable_status'), 'inactive')))
        );
    }

    public function testWhereNotEqualsNull()
    {
        $criteria = $this->mapper->newCriteria()
                ->where('nullableStatus', '!=', null);

        $this->assertMappedSelect($criteria,
                $this->selectAllColumns()
                        ->where(Expr::isNotNull($this->column('nullable_status')))
        );
    }
}
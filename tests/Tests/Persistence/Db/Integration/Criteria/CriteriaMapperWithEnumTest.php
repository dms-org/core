<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Criteria;

use Dms\Core\Persistence\Db\Criteria\CriteriaMapper;
use Dms\Core\Persistence\Db\Mapping\CustomOrm;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\Enum\EntityWithEnumMapper;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\Enum\StatusEnum;

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
                        ->where(Expr::equal($this->column('nullable_status'), Expr::param(null, 'active')))
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
                                Expr::param(null, 'inactive')))
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
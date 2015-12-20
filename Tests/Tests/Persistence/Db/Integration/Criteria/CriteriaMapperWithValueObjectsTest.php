<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Criteria;

use Dms\Core\Exception\TypeMismatchException;
use Dms\Core\Persistence\Db\Criteria\CriteriaMapper;
use Dms\Core\Persistence\Db\Mapping\CustomOrm;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\NestedValueObject\LevelOne;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\NestedValueObject\LevelThree;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\NestedValueObject\LevelTwo;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\NestedValueObject\ParentEntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CriteriaMapperWithValueObjectsTest extends CriteriaMapperTestBase
{

    protected function buildMapper()
    {
        return new CriteriaMapper(new ParentEntityMapper(CustomOrm::from([])));
    }

    public function testWhereEquals()
    {
        $criteria = $this->mapper->newCriteria()
                ->where('one', '=', new LevelOne(new LevelTwo(new LevelThree('123'))));

        $this->assertMappedSelect($criteria,
                $this->selectAllColumns()
                        ->where(Expr::equal($this->column('one_two_three_value'),
                                Expr::param($this->columnType('one_two_three_value'), '123')))
        );
    }

    public function testWhereEqualsNull()
    {
        // Should throw be 'one' is a non-nullable property
        $this->setExpectedException(TypeMismatchException::class);

        $this->mapper->newCriteria()
                ->where('one', '=', null);
    }

    public function testWhereEqualsWithPartialNestedProperty()
    {
        $criteria = $this->mapper->newCriteria()
                ->where('one.two', '!=', new LevelTwo(new LevelThree('123')));

        $this->assertMappedSelect($criteria,
                $this->selectAllColumns()
                        ->where(Expr::notEqual($this->column('one_two_three_value'),
                                Expr::param($this->columnType('one_two_three_value'), '123')))
        );
    }

    public function testWhereEqualsWithNestedProperty()
    {
        $criteria = $this->mapper->newCriteria()
                ->where('one.two.three.val', '=', '123');

        $this->assertMappedSelect($criteria,
                $this->selectAllColumns()
                        ->where(Expr::equal($this->column('one_two_three_value'),
                                Expr::param($this->columnType('one_two_three_value'), '123')))
        );
    }

    public function testWhereNotEquals()
    {
        $criteria = $this->mapper->newCriteria()
                ->where('one', '!=', new LevelOne(new LevelTwo(new LevelThree('123'))));

        $this->assertMappedSelect($criteria,
                $this->selectAllColumns()
                        ->where(Expr::notEqual($this->column('one_two_three_value'),
                                Expr::param($this->columnType('one_two_three_value'), '123')))
        );
    }

    public function testOrderByNestedProperty()
    {
        $criteria = $this->mapper->newCriteria()
                ->orderByDesc('one.two.three.val');

        $this->assertMappedSelect($criteria,
                $this->selectAllColumns()
                        ->orderByDesc($this->column('one_two_three_value'))
        );
    }
}
<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Criteria;

use Dms\Core\Model\Criteria\SpecificationDefinition;
use Dms\Core\Persistence\Db\Criteria\CriteriaMapper;
use Dms\Core\Persistence\Db\Mapping\CustomOrm;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Inheritance\Fixtures\TableInheritance\SingleTable\TestSingleTableInheritanceMapper;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Inheritance\Fixtures\TableInheritance\TestSubclassEntity1;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Inheritance\Fixtures\TableInheritance\TestSubclassEntity2;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Inheritance\Fixtures\TableInheritance\TestSubclassEntity3;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CriteriaMapperWithSingleTableInheritanceTest extends CriteriaMapperTestBase
{

    protected function buildMapper()
    {
        return new CriteriaMapper(new TestSingleTableInheritanceMapper(CustomOrm::from([])));
    }

    public function testInstanceOfWithSingleTableInheritance()
    {
        $criteria = $this->mapper->newCriteria()
                ->whereInstanceOf(TestSubclassEntity1::class);

        $this->assertMappedSelect($criteria,
                $this->select()
                        ->addColumn('id', $this->column('id'))
                        ->addColumn('base_prop', $this->column('base_prop'))
                        ->addColumn('class_type', $this->column('class_type'))
                        ->addColumn('subclass1_prop', $this->column('subclass1_prop'))
                        ->addColumn('subclass3_prop', $this->column('subclass3_prop'))
                        ->where(Expr::equal($this->column('class_type'), Expr::param($this->columnType('class_type'), 'subclass1')))
        );

        $criteria = $this->mapper->newCriteria()
                ->whereInstanceOf(TestSubclassEntity2::class);

        $this->assertMappedSelect($criteria,
                $this->select()
                        ->addColumn('id', $this->column('id'))
                        ->addColumn('base_prop', $this->column('base_prop'))
                        ->addColumn('class_type', $this->column('class_type'))
                        ->addColumn('subclass2_prop', $this->column('subclass2_prop'))
                        ->addColumn('subclass2_prop2', $this->column('subclass2_prop2'))
                        ->where(Expr::equal($this->column('class_type'), Expr::param($this->columnType('class_type'), 'subclass2')))
        );
    }

    public function testInstanceOfDeepWithSingleTableInheritance()
    {
        $criteria = $this->mapper->newCriteria()
                ->whereInstanceOf(TestSubclassEntity3::class);

        $this->assertMappedSelect($criteria,
                $this->select()
                        ->addColumn('id', $this->column('id'))
                        ->addColumn('base_prop', $this->column('base_prop'))
                        ->addColumn('class_type', $this->column('class_type'))
                        ->addColumn('subclass3_prop', $this->column('subclass3_prop'))
                        ->where(Expr::equal($this->column('class_type'), Expr::param($this->columnType('class_type'), 'subclass3')))
        );
    }

    public function testComplexInstanceOf()
    {
        $criteria = $this->mapper->newCriteria()
                ->whereAny(function(SpecificationDefinition $match) {
                    $match->whereInstanceOf(TestSubclassEntity2::class);
                    $match->whereInstanceOf(TestSubclassEntity3::class);
                });

        $this->assertMappedSelect($criteria,
                $this->select()
                        ->addColumn('id', $this->column('id'))
                        ->addColumn('base_prop', $this->column('base_prop'))
                        ->addColumn('class_type', $this->column('class_type'))
                        ->addColumn('subclass2_prop', $this->column('subclass2_prop'))
                        ->addColumn('subclass2_prop2', $this->column('subclass2_prop2'))
                        ->addColumn('subclass3_prop', $this->column('subclass3_prop'))
                        ->addColumn('subclass1_prop', $this->column('subclass1_prop'))
                        ->where(Expr::or_(
                                Expr::equal($this->column('class_type'), Expr::param($this->columnType('class_type'), 'subclass2')),
                                Expr::equal($this->column('class_type'), Expr::param($this->columnType('class_type'), 'subclass3'))
                        ))
        );
    }
}
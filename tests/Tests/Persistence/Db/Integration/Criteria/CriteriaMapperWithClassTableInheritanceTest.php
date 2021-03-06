<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Criteria;

use Dms\Core\Model\Criteria\SpecificationDefinition;
use Dms\Core\Persistence\Db\Criteria\CriteriaMapper;
use Dms\Core\Persistence\Db\Mapping\CustomOrm;
use Dms\Core\Persistence\Db\Query\Clause\Join;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Persistence\Db\Schema\Table;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Inheritance\Fixtures\TableInheritance\ClassPerTable\TestClassTableInheritanceMapper;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Inheritance\Fixtures\TableInheritance\TestSubclassEntity1;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Inheritance\Fixtures\TableInheritance\TestSubclassEntity2;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Inheritance\Fixtures\TableInheritance\TestSubclassEntity3;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Inheritance\Fixtures\TableInheritance\TestSuperclassEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CriteriaMapperWithClassTableInheritanceTest extends CriteriaMapperTestBase
{
    protected function buildMapper()
    {
        $mapper = new TestClassTableInheritanceMapper(CustomOrm::from([
            TestSuperclassEntity::class => TestClassTableInheritanceMapper::class
        ]));

        return new CriteriaMapper($mapper);
    }

    public function testInstanceOfWithClassTableInheritance()
    {
        $criteria = $this->mapper->newCriteria()
                ->whereInstanceOf(TestSubclassEntity1::class);

        $this->assertMappedSelect($criteria,
                $this->select()
                        ->addColumn('id', Expr::tableColumn($this->tables['parent_entities'], 'id'))
                        ->addColumn('base_prop', Expr::tableColumn($this->tables['parent_entities'], 'base_prop'))
                        ->addColumn('__subclass1_table__id', Expr::tableColumn($this->tables['subclass1_table'], 'id'))
                        ->addColumn('__subclass1_table__subclass1_prop', Expr::tableColumn($this->tables['subclass1_table'], 'subclass1_prop'))
                        ->join(Join::inner($this->tables['subclass1_table'], 'subclass1_table', [
                            Expr::equal(
                                    $this->column('id'),
                                    Expr::tableColumn($this->tables['subclass1_table'], 'id')
                            )
                        ]))
        );
    }

    public function testInstanceOfDeepWithClassTableInheritance()
    {
        $criteria = $this->mapper->newCriteria()
                ->whereInstanceOf(TestSubclassEntity3::class);

        $this->assertMappedSelect($criteria,
                $this->select()
                        ->addColumn('id', Expr::tableColumn($this->tables['parent_entities'], 'id'))
                        ->addColumn('base_prop', Expr::tableColumn($this->tables['parent_entities'], 'base_prop'))
                        ->addColumn('__subclass1_table__id', Expr::tableColumn($this->tables['subclass1_table'], 'id'))
                        ->addColumn('__subclass1_table__subclass1_prop', Expr::tableColumn($this->tables['subclass1_table'], 'subclass1_prop'))
                        ->addColumn('__subclass3_table__id', Expr::tableColumn($this->tables['subclass3_table'], 'id'))
                        ->addColumn('__subclass3_table__subclass3_prop', Expr::tableColumn($this->tables['subclass3_table'], 'subclass3_prop'))
                        ->join(Join::inner($this->tables['subclass1_table'], 'subclass1_table', [
                                Expr::equal(
                                        $this->column('id'),
                                        Expr::tableColumn($this->tables['subclass1_table'], 'id')
                                )
                        ]))
                        ->join(Join::inner($this->tables['subclass3_table'], 'subclass3_table', [
                                Expr::equal(
                                        Expr::tableColumn($this->tables['subclass1_table'], 'id'),
                                        Expr::tableColumn($this->tables['subclass3_table'], 'id')
                                )
                        ]))
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
                        ->addColumn('id', Expr::tableColumn($this->tables['parent_entities'], 'id'))
                        ->addColumn('base_prop', Expr::tableColumn($this->tables['parent_entities'], 'base_prop'))
                        ->addColumn('__subclass1_table__id', Expr::tableColumn($this->tables['subclass1_table'], 'id'))
                        ->addColumn('__subclass1_table__subclass1_prop', Expr::tableColumn($this->tables['subclass1_table'], 'subclass1_prop'))
                        ->addColumn('__subclass3_table__id', Expr::tableColumn($this->tables['subclass3_table'], 'id'))
                        ->addColumn('__subclass3_table__subclass3_prop', Expr::tableColumn($this->tables['subclass3_table'], 'subclass3_prop'))
                        ->addColumn('__subclass2_table__id', Expr::tableColumn($this->tables['subclass2_table'], 'id'))
                        ->addColumn('__subclass2_table__subclass2_prop', Expr::tableColumn($this->tables['subclass2_table'], 'subclass2_prop'))
                        ->addColumn('__subclass2_table__subclass2_prop2', Expr::tableColumn($this->tables['subclass2_table'], 'subclass2_prop2'))
                        ->join(Join::left($this->tables['subclass1_table'], 'subclass1_table', [
                                Expr::equal(
                                        $this->column('id'),
                                        Expr::tableColumn($this->tables['subclass1_table'], 'id')
                                )
                        ]))
                        ->join(Join::left($this->tables['subclass3_table'], 'subclass3_table', [
                                Expr::equal(
                                        Expr::tableColumn($this->tables['subclass1_table'], 'id'),
                                        Expr::tableColumn($this->tables['subclass3_table'], 'id')
                                )
                        ]))
                        ->join(Join::left($this->tables['subclass2_table'], 'subclass2_table', [
                                Expr::equal(
                                        Expr::tableColumn($this->tables['parent_entities'], 'id'),
                                        Expr::tableColumn($this->tables['subclass2_table'], 'id')
                                )
                        ]))
                        ->where(Expr::or_(
                                Expr::isNotNull(Expr::tableColumn($this->tables['subclass2_table'], 'id')),
                                Expr::isNotNull(Expr::tableColumn($this->tables['subclass3_table'], 'id'))
                        ))
        );
    }
}
<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\ToMany;

use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Persistence\Db\Query\Select;
use Dms\Core\Persistence\Db\Schema\ForeignKeyMode;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToManyRelation\ChildEntity;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToManyRelation\Ordered\ParentWithOrderedChildrenEntityMapper;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToManyRelation\ParentEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class OrderedToManyRelationTest extends ToManyRelationTestBase
{
    /**
     * @inheritDoc
     */
    protected function loadOrm()
    {
        return ParentWithOrderedChildrenEntityMapper::orm();
    }

    /**
     * @inheritDoc
     */
    protected function deleteForeignKeyMode()
    {
        return ForeignKeyMode::SET_NULL;
    }

    public function testLoadsInCorrectOrder()
    {
        $this->setDataInDb([
                'parent_entities' => [
                        ['id' => 1],
                        ['id' => 2],
                        ['id' => 3],
                ],
                'child_entities'  => [
                        ['id' => 1, 'parent_id' => 1, 'val' => -1],
                        ['id' => 2, 'parent_id' => 1, 'val' => -5],
                        ['id' => 3, 'parent_id' => 1, 'val' => 30],
                        //
                        ['id' => 4, 'parent_id' => 2, 'val' => 999],
                        ['id' => 5, 'parent_id' => 2, 'val' => 99],
                        ['id' => 6, 'parent_id' => 2, 'val' => 9],
                        //
                        ['id' => 7, 'parent_id' => 3, 'val' => 1],
                        ['id' => 8, 'parent_id' => 3, 'val' => 3],
                        ['id' => 9, 'parent_id' => 3, 'val' => 2],
                ]
        ]);

        $entities = [
                new ParentEntity(1, [
                        new ChildEntity(2, -5),
                        new ChildEntity(1, -1),
                        new ChildEntity(3, 30),
                ]),
                new ParentEntity(2, [
                        new ChildEntity(6, 9),
                        new ChildEntity(5, 99),
                        new ChildEntity(4, 999),
                ]),
                new ParentEntity(3, [
                        new ChildEntity(7, 1),
                        new ChildEntity(9, 2),
                        new ChildEntity(8, 3),
                ]),
        ];

        $this->assertEquals($entities, $this->repo->getAll());

        $this->assertExecutedQueryTypes([
                'Load all parent entities' => Select::class,
                'Load all child entities'  => Select::class,
        ]);

        $this->assertExecutedQueryNumber(2,
                Select::allFrom($this->childEntities)
                        ->where(Expr::in(
                                Expr::tableColumn($this->childEntities, 'parent_id'),
                                Expr::tuple([Expr::idParam(1), Expr::idParam(2), Expr::idParam(3)])
                        ))
                        ->orderByAsc(Expr::tableColumn($this->childEntities, 'val'))
        );
    }
}
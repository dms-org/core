<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Hook;

use Dms\Core\Persistence\Db\Mapping\IOrm;
use Dms\Core\Persistence\Db\Query\Delete;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Persistence\Db\Query\Reorder;
use Dms\Core\Persistence\Db\Query\ResequenceOrderIndexColumn;
use Dms\Core\Persistence\Db\Query\Select;
use Dms\Core\Persistence\Db\Query\Update;
use Dms\Core\Persistence\Db\Query\Upsert;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\DbIntegrationTest;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Hook\Fixtures\GroupedOrderIndex\OrderedEntity;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Hook\Fixtures\GroupedOrderIndex\OrderedEntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class GroupedOrderIndexTest extends DbIntegrationTest
{
    /**
     * @return IOrm
     */
    protected function loadOrm()
    {
        return OrderedEntityMapper::orm();
    }

    public function testPersistNew()
    {
        $this->repo->saveAll([
                $e1 = new OrderedEntity(null, 'group-1'),
                $e2 = new OrderedEntity(null, 'group-1'),
                $e3 = new OrderedEntity(null, 'group-1'),
                //
                $e4 = new OrderedEntity(null, 'group-2'),
                $e5 = new OrderedEntity(null, 'group-2'),
                $e6 = new OrderedEntity(null, 'group-2'),
                //
                $e7 = new OrderedEntity(null, 'group-3'),
                $e8 = new OrderedEntity(null, 'group-3'),
                $e9 = new OrderedEntity(null, 'group-3'),
        ]);

        $this->assertDatabaseDataSameAs([
                'data' => [
                        ['id' => 1, 'group' => 'group-1', 'order_index' => 1],
                        ['id' => 2, 'group' => 'group-1', 'order_index' => 2],
                        ['id' => 3, 'group' => 'group-1', 'order_index' => 3],
                        //
                        ['id' => 4, 'group' => 'group-2', 'order_index' => 1],
                        ['id' => 5, 'group' => 'group-2', 'order_index' => 2],
                        ['id' => 6, 'group' => 'group-2', 'order_index' => 3],
                        //
                        ['id' => 7, 'group' => 'group-3', 'order_index' => 1],
                        ['id' => 8, 'group' => 'group-3', 'order_index' => 2],
                        ['id' => 9, 'group' => 'group-3', 'order_index' => 3],
                ]
        ]);

        $this->assertSame(1, $e1->orderIndex);
        $this->assertSame(2, $e2->orderIndex);
        $this->assertSame(3, $e3->orderIndex);

        $this->assertSame(1, $e4->orderIndex);
        $this->assertSame(2, $e5->orderIndex);
        $this->assertSame(3, $e6->orderIndex);

        $this->assertSame(1, $e7->orderIndex);
        $this->assertSame(2, $e8->orderIndex);
        $this->assertSame(3, $e9->orderIndex);

        $this->assertExecutedQueryTypes([
                'Load current max order index' => Select::class,
                'Insert rows'                  => Upsert::class,
        ]);

        $table = $this->table->getStructure();
        $this->assertExecutedQueryNumber(1,
                Select::from($table)
                        ->addColumn('order_index', Expr::max(Expr::tableColumn($table, 'order_index')))
                        ->addRawColumn('group')
                        ->where(Expr::in(
                                Expr::tableColumn($table, 'group'),
                                Expr::tupleParams($table->getColumn('group')->getType(), ['group-1', 'group-2', 'group-3'])
                        ))
                        ->addGroupBy(Expr::tableColumn($table, 'group'))
        );
    }

    public function testPersistExisting()
    {
        $this->db->setData([
                'data' => [
                        ['id' => 1, 'group' => 'group-1', 'order_index' => 1],
                        ['id' => 2, 'group' => 'group-1', 'order_index' => 2],
                        ['id' => 3, 'group' => 'group-1', 'order_index' => 3],
                        //
                        ['id' => 4, 'group' => 'group-2', 'order_index' => 1],
                        ['id' => 5, 'group' => 'group-2', 'order_index' => 2],
                        ['id' => 6, 'group' => 'group-2', 'order_index' => 3],
                ]
        ]);

        $this->repo->saveAll([
                $e1 = new OrderedEntity(1, 'group-1', 10),
                $e2 = new OrderedEntity(2, 'group-1', 11),
                $e3 = new OrderedEntity(3, 'group-1', 12),
        ]);

        $this->assertDatabaseDataSameAs([
                'data' => [
                        ['id' => 1, 'group' => 'group-1', 'order_index' => 10],
                        ['id' => 2, 'group' => 'group-1', 'order_index' => 11],
                        ['id' => 3, 'group' => 'group-1', 'order_index' => 12],
                        //
                        ['id' => 4, 'group' => 'group-2', 'order_index' => 1],
                        ['id' => 5, 'group' => 'group-2', 'order_index' => 2],
                        ['id' => 6, 'group' => 'group-2', 'order_index' => 3],
                ]
        ]);

        $this->assertSame(10, $e1->orderIndex);
        $this->assertSame(11, $e2->orderIndex);
        $this->assertSame(12, $e3->orderIndex);
    }


    public function testPersistNewToExistingGroups()
    {
        $this->db->setData([
                'data' => [
                        ['id' => 1, 'group' => 'group-1', 'order_index' => 1],
                        ['id' => 2, 'group' => 'group-1', 'order_index' => 2],
                        //
                        ['id' => 4, 'group' => 'group-2', 'order_index' => 1],
                        ['id' => 5, 'group' => 'group-2', 'order_index' => 2],
                        ['id' => 6, 'group' => 'group-2', 'order_index' => 3],
                ]
        ]);

        $this->repo->saveAll([
                $e1 = new OrderedEntity(null, 'group-1'),
                $e2 = new OrderedEntity(null, 'group-1'),
                $e3 = new OrderedEntity(null, 'group-1'),
                //
                $e4 = new OrderedEntity(null, 'group-2'),
                $e5 = new OrderedEntity(null, 'group-2'),
                $e6 = new OrderedEntity(null, 'group-2'),
        ]);

        $this->assertExecutedQueryTypes([
                'Load current max order index' => Select::class,
                'Insert rows'                  => Upsert::class,
        ]);

        $this->assertDatabaseDataSameAs([
                'data' => [
                        ['id' => 1, 'group' => 'group-1', 'order_index' => 1],
                        ['id' => 2, 'group' => 'group-1', 'order_index' => 2],
                        //
                        ['id' => 4, 'group' => 'group-2', 'order_index' => 1],
                        ['id' => 5, 'group' => 'group-2', 'order_index' => 2],
                        ['id' => 6, 'group' => 'group-2', 'order_index' => 3],
                        //
                        ['id' => 7, 'group' => 'group-1', 'order_index' => 3],
                        ['id' => 8, 'group' => 'group-1', 'order_index' => 4],
                        ['id' => 9, 'group' => 'group-1', 'order_index' => 5],
                        //
                        ['id' => 10, 'group' => 'group-2', 'order_index' => 4],
                        ['id' => 11, 'group' => 'group-2', 'order_index' => 5],
                        ['id' => 12, 'group' => 'group-2', 'order_index' => 6],
                ]
        ]);

        $this->assertSame(3, $e1->orderIndex);
        $this->assertSame(4, $e2->orderIndex);
        $this->assertSame(5, $e3->orderIndex);

        $this->assertSame(4, $e4->orderIndex);
        $this->assertSame(5, $e5->orderIndex);
        $this->assertSame(6, $e6->orderIndex);
    }

    public function testRemoveResequencesOrderIndex()
    {
        $this->db->setData([
                'data' => [
                        ['id' => 1, 'group' => 'group-1', 'order_index' => 1],
                        ['id' => 2, 'group' => 'group-1', 'order_index' => 2],
                        ['id' => 3, 'group' => 'group-1', 'order_index' => 3],
                        //
                        ['id' => 4, 'group' => 'group-2', 'order_index' => 1],
                        ['id' => 5, 'group' => 'group-2', 'order_index' => 2],
                        ['id' => 6, 'group' => 'group-2', 'order_index' => 3],
                        //
                        ['id' => 7, 'group' => 'group-3', 'order_index' => 1],
                        ['id' => 8, 'group' => 'group-3', 'order_index' => 2],
                        ['id' => 9, 'group' => 'group-3', 'order_index' => 3],
                ]
        ]);

        $this->repo->removeAllById([2, 4, 7, 8]);


        $this->assertDatabaseDataSameAs([
                'data' => [
                        ['id' => 1, 'group' => 'group-1', 'order_index' => 1],
                        ['id' => 3, 'group' => 'group-1', 'order_index' => 2],
                        //
                        ['id' => 5, 'group' => 'group-2', 'order_index' => 1],
                        ['id' => 6, 'group' => 'group-2', 'order_index' => 2],
                        //
                        ['id' => 9, 'group' => 'group-3', 'order_index' => 1],
                ]
        ]);

        $this->assertExecutedQueryTypes([
                'Delete entities'        => Delete::class,
                'Resequence order index' => ResequenceOrderIndexColumn::class,
        ]);

        $this->assertExecutedQueryNumber(2, new ResequenceOrderIndexColumn(
                $this->table->getStructure(),
                'order_index',
                'group'
        ));
    }

    public function testReorderRowBackwards()
    {
        $this->db->setData([
                'data' => [
                        ['id' => 1, 'group' => 'group-1', 'order_index' => 1],
                        ['id' => 2, 'group' => 'group-1', 'order_index' => 2],
                        ['id' => 3, 'group' => 'group-1', 'order_index' => 3],
                        //
                        ['id' => 4, 'group' => 'group-2', 'order_index' => 1],
                        ['id' => 5, 'group' => 'group-2', 'order_index' => 2],
                        ['id' => 6, 'group' => 'group-2', 'order_index' => 3],
                ]
        ]);

        (new Reorder($this->table->getStructure(), 'order_index'))
                ->withPrimaryKey(6)
                ->toNewIndex(1)
                ->groupedBy('group')
                ->executeOn($this->connection);


        $this->assertDatabaseDataSameAs([
                'data' => [
                        ['id' => 1, 'group' => 'group-1', 'order_index' => 1],
                        ['id' => 2, 'group' => 'group-1', 'order_index' => 2],
                        ['id' => 3, 'group' => 'group-1', 'order_index' => 3],
                        //
                        ['id' => 4, 'group' => 'group-2', 'order_index' => 2],
                        ['id' => 5, 'group' => 'group-2', 'order_index' => 3],
                        ['id' => 6, 'group' => 'group-2', 'order_index' => 1],
                ]
        ]);

        $this->assertExecutedQueryTypes([
                'Load current index and group' => Select::class,
                'Shift other rows'             => Update::class,
                'Update to new index'          => Update::class,
        ]);
    }

    public function testReorderRowForwards()
    {
        $this->db->setData([
                'data' => [
                        ['id' => 1, 'group' => 'group-1', 'order_index' => 1],
                        ['id' => 2, 'group' => 'group-1', 'order_index' => 2],
                        ['id' => 3, 'group' => 'group-1', 'order_index' => 3],
                        //
                        ['id' => 4, 'group' => 'group-2', 'order_index' => 1],
                        ['id' => 5, 'group' => 'group-2', 'order_index' => 2],
                        ['id' => 6, 'group' => 'group-2', 'order_index' => 3],
                ]
        ]);

        (new Reorder($this->table->getStructure(), 'order_index'))
                ->withPrimaryKey(1)
                ->toNewIndex(2)
                ->groupedBy('group')
                ->executeOn($this->connection);


        $this->assertDatabaseDataSameAs([
                'data' => [
                        ['id' => 1, 'group' => 'group-1', 'order_index' => 2],
                        ['id' => 2, 'group' => 'group-1', 'order_index' => 1],
                        ['id' => 3, 'group' => 'group-1', 'order_index' => 3],
                        //
                        ['id' => 4, 'group' => 'group-2', 'order_index' => 1],
                        ['id' => 5, 'group' => 'group-2', 'order_index' => 2],
                        ['id' => 6, 'group' => 'group-2', 'order_index' => 3],
                ]
        ]);

        $this->assertExecutedQueryTypes([
                'Load current index and group' => Select::class,
                'Shift other rows'             => Update::class,
                'Update to new index'          => Update::class,
        ]);
    }
}
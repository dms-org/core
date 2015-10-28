<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Hook;

use Iddigital\Cms\Core\Persistence\Db\Mapping\IOrm;
use Iddigital\Cms\Core\Persistence\Db\Query\Delete;
use Iddigital\Cms\Core\Persistence\Db\Query\Reorder;
use Iddigital\Cms\Core\Persistence\Db\Query\ResequenceOrderIndexColumn;
use Iddigital\Cms\Core\Persistence\Db\Query\Select;
use Iddigital\Cms\Core\Persistence\Db\Query\Update;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\DbIntegrationTest;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Hook\Fixtures\GlobalOrderIndex\OrderedEntity;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Hook\Fixtures\GlobalOrderIndex\OrderedEntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class GlobalOrderIndexTest extends DbIntegrationTest
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
                $e1 = new OrderedEntity(),
                $e2 = new OrderedEntity(),
                $e3 = new OrderedEntity(),
        ]);

        $this->assertDatabaseDataSameAs([
                'data' => [
                        ['id' => 1, 'order_index' => 1],
                        ['id' => 2, 'order_index' => 2],
                        ['id' => 3, 'order_index' => 3],
                ]
        ]);

        $this->assertSame(1, $e1->orderIndex);
        $this->assertSame(2, $e2->orderIndex);
        $this->assertSame(3, $e3->orderIndex);
    }

    public function testPersistExisting()
    {
        $this->db->setData([
                'data' => [
                        ['id' => 1, 'order_index' => 1],
                        ['id' => 2, 'order_index' => 2],
                        ['id' => 3, 'order_index' => 3],
                ]
        ]);

        $this->repo->saveAll([
                $e1 = new OrderedEntity(1, 2),
                $e2 = new OrderedEntity(2, 3),
                $e3 = new OrderedEntity(3, 4),
        ]);

        $this->assertDatabaseDataSameAs([
                'data' => [
                        ['id' => 1, 'order_index' => 2],
                        ['id' => 2, 'order_index' => 3],
                        ['id' => 3, 'order_index' => 4],
                ]
        ]);

        $this->assertSame(2, $e1->orderIndex);
        $this->assertSame(3, $e2->orderIndex);
        $this->assertSame(4, $e3->orderIndex);
    }

    public function testPersistNewToExisting()
    {
        $this->db->setData([
                'data' => [
                        ['id' => 1, 'order_index' => 1],
                        ['id' => 2, 'order_index' => 2],
                ]
        ]);

        $this->repo->saveAll([
                $e1 = new OrderedEntity(),
                $e2 = new OrderedEntity(),
                $e3 = new OrderedEntity(),
        ]);

        $this->assertDatabaseDataSameAs([
                'data' => [
                        ['id' => 1, 'order_index' => 1],
                        ['id' => 2, 'order_index' => 2],
                        ['id' => 3, 'order_index' => 3],
                        ['id' => 4, 'order_index' => 4],
                        ['id' => 5, 'order_index' => 5],
                ]
        ]);

        $this->assertSame(3, $e1->orderIndex);
        $this->assertSame(4, $e2->orderIndex);
        $this->assertSame(5, $e3->orderIndex);
    }

    public function testRemoveResequencesOrderIndex()
    {
        $this->db->setData([
                'data' => [
                        ['id' => 1, 'order_index' => 1],
                        ['id' => 2, 'order_index' => 2],
                        ['id' => 3, 'order_index' => 3],
                        ['id' => 4, 'order_index' => 4],
                        ['id' => 5, 'order_index' => 5],
                ]
        ]);

        $this->repo->removeAllById([2, 4]);

        $this->assertDatabaseDataSameAs([
                'data' => [
                        ['id' => 1, 'order_index' => 1],
                        ['id' => 3, 'order_index' => 2],
                        ['id' => 5, 'order_index' => 3],
                ]
        ]);

        $this->assertExecutedQueryTypes([
                'Delete entities'        => Delete::class,
                'Resequence order index' => ResequenceOrderIndexColumn::class,
        ]);

        $this->assertExecutedQueryNumber(2, new ResequenceOrderIndexColumn(
                $this->table->getStructure(),
                'order_index'
        ));
    }

    public function testReorderRowBackwards()
    {
        $this->db->setData([
                'data' => [
                        ['id' => 1, 'order_index' => 1],
                        ['id' => 2, 'order_index' => 2],
                        ['id' => 3, 'order_index' => 3],
                        ['id' => 4, 'order_index' => 4],
                        ['id' => 5, 'order_index' => 5],
                ]
        ]);

        (new Reorder($this->table->getStructure(), 'order_index'))
                ->withPrimaryKey(5)
                ->toNewIndex(1)
                ->executeOn($this->connection);


        $this->assertDatabaseDataSameAs([
                'data' => [
                        ['id' => 1, 'order_index' => 2],
                        ['id' => 2, 'order_index' => 3],
                        ['id' => 3, 'order_index' => 4],
                        ['id' => 4, 'order_index' => 5],
                        ['id' => 5, 'order_index' => 1],
                ]
        ]);

        $this->assertExecutedQueryTypes([
                'Load current index'  => Select::class,
                'Shift other rows'    => Update::class,
                'Update to new index' => Update::class,
        ]);
    }

    public function testReorderRowForwards()
    {
        $this->db->setData([
                'data' => [
                        ['id' => 1, 'order_index' => 1],
                        ['id' => 2, 'order_index' => 2],
                        ['id' => 3, 'order_index' => 3],
                        ['id' => 4, 'order_index' => 4],
                        ['id' => 5, 'order_index' => 5],
                ]
        ]);

        (new Reorder($this->table->getStructure(), 'order_index'))
                ->withPrimaryKey(2)
                ->toNewIndex(4)
                ->executeOn($this->connection);


        $this->assertDatabaseDataSameAs([
                'data' => [
                        ['id' => 1, 'order_index' => 1],
                        ['id' => 2, 'order_index' => 4],
                        ['id' => 3, 'order_index' => 2],
                        ['id' => 4, 'order_index' => 3],
                        ['id' => 5, 'order_index' => 5],
                ]
        ]);

        $this->assertExecutedQueryTypes([
                'Load current index'  => Select::class,
                'Shift other rows'    => Update::class,
                'Update to new index' => Update::class,
        ]);
    }
}
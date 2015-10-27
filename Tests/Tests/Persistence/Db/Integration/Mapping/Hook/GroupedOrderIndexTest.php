<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Hook;

use Iddigital\Cms\Core\Persistence\Db\Mapping\IOrm;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\DbIntegrationTest;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Hook\Fixtures\GroupedOrderIndex\OrderedEntity;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Hook\Fixtures\GroupedOrderIndex\OrderedEntityMapper;

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
}
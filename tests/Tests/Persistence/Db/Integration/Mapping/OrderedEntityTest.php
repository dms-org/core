<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping;

use Dms\Core\Persistence\Db\Mapping\CustomOrm;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\Enum\GenderEnum;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\Enum\StatusEnum;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\Ordering\TestGroupEnum;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\Ordering\TestOrderedEntity;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\Ordering\TestOrderedEntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class OrderedEntityTest extends DbIntegrationTest
{
    /**
     * @inheritDoc
     */
    protected function loadOrm()
    {
        return CustomOrm::from([TestOrderedEntity::class => TestOrderedEntityMapper::class]);
    }

    public function testPersistEntities()
    {
        $this->repo->saveAll([
            new TestOrderedEntity(TestGroupEnum::one()),
            new TestOrderedEntity(TestGroupEnum::one()),
            new TestOrderedEntity(TestGroupEnum::one()),
            //
            new TestOrderedEntity(TestGroupEnum::two()),
            new TestOrderedEntity(TestGroupEnum::two()),
            //
            new TestOrderedEntity(TestGroupEnum::three()),
        ]);

        $this->assertDatabaseDataSameAs([
            'data' => [
                ['id' => 1, 'group' => 'one', 'order_index' => 1],
                ['id' => 2, 'group' => 'one', 'order_index' => 2],
                ['id' => 3, 'group' => 'one', 'order_index' => 3],
                //
                ['id' => 4, 'group' => 'two', 'order_index' => 1],
                ['id' => 5, 'group' => 'two', 'order_index' => 2],
                //
                ['id' => 6, 'group' => 'three', 'order_index' => 1],
            ],
        ]);
    }

    public function testReorderWithASingleGroup()
    {
        $this->repo->saveAll([
            new TestOrderedEntity(TestGroupEnum::one()),
            new TestOrderedEntity(TestGroupEnum::one()),
            new TestOrderedEntity(TestGroupEnum::one()),
        ]);

        $this->repo->reorderOnProperty(3, 1, TestOrderedEntity::ORDER);

        $this->assertDatabaseDataSameAs([
            'data' => [
                ['id' => 1, 'group' => 'one', 'order_index' => 2],
                ['id' => 2, 'group' => 'one', 'order_index' => 3],
                ['id' => 3, 'group' => 'one', 'order_index' => 1],
            ],
        ]);
    }

    public function testReorderWithMultipleGroups()
    {
        $this->repo->saveAll([
            new TestOrderedEntity(TestGroupEnum::one()),
            new TestOrderedEntity(TestGroupEnum::one()),
            new TestOrderedEntity(TestGroupEnum::one()),
            //
            new TestOrderedEntity(TestGroupEnum::two()),
            new TestOrderedEntity(TestGroupEnum::two()),
        ]);

        $this->repo->reorderOnProperty(1, 3, TestOrderedEntity::ORDER, TestOrderedEntity::GROUP);

        $this->assertDatabaseDataSameAs([
            'data' => [
                ['id' => 1, 'group' => 'one', 'order_index' => 3],
                ['id' => 2, 'group' => 'one', 'order_index' => 1],
                ['id' => 3, 'group' => 'one', 'order_index' => 2],
                //
                ['id' => 4, 'group' => 'two', 'order_index' => 1],
                ['id' => 5, 'group' => 'two', 'order_index' => 2],
            ],
        ]);
    }
}
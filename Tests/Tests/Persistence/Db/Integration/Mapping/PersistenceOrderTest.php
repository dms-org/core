<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping;

use Iddigital\Cms\Core\Persistence\Db\Mapping\CustomOrm;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IEntityMapper;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\PropertyTypes\PropertyTypesEntity;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\PropertyTypes\PropertyTypesMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PersistenceOrderTest extends DbIntegrationTest
{
    /**
     * @inheritDoc
     */
    protected function loadOrm()
    {
        return CustomOrm::from([PropertyTypesEntity::class => PropertyTypesMapper::class]);
    }

    public function testWillPerformUpdatesBeforeInserts()
    {
        $this->repo->saveAll([
                new PropertyTypesEntity(null, '1'),
                new PropertyTypesEntity(null, '2'),
                new PropertyTypesEntity(1, '3')
        ]);

        $this->assertDatabaseDataSameAs([
            'property_types' => [
                    ['id' => 1, 'value' => '3', 'value_upper' => '3', 'value_lower' => '3'],
                    ['id' => 2, 'value' => '1', 'value_upper' => '1', 'value_lower' => '1'],
                    ['id' => 3, 'value' => '2', 'value_upper' => '2', 'value_lower' => '2'],
            ]
        ]);
    }
}
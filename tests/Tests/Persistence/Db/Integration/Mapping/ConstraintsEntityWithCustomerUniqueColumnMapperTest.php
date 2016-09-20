<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping;

use Dms\Core\Persistence\Db\Mapping\CustomOrm;
use Dms\Core\Persistence\Db\Mapping\IOrm;
use Dms\Core\Persistence\Db\Schema\Index;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\Constraints\ConstrainedEntity;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\Constraints\ConstraintsEntityWithCustomUniqueColumnMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ConstraintsEntityWithCustomerUniqueColumnEntityMapperTest extends DbIntegrationTest
{
    public function setUp()
    {
        $this->mapper = $this->loadOrm()->getEntityMapper(ConstrainedEntity::class);
    }

    /**
     * @return IOrm
     */
    protected function loadOrm()
    {
        return CustomOrm::from([ConstrainedEntity::class => ConstraintsEntityWithCustomUniqueColumnMapper::class]);
    }

    public function testLoadsCorrectIndexes()
    {
        $this->assertEquals(
            [
                'index_name'        => new Index('index_name', false, ['indexed']),
                'unique_index_name' => new Index('unique_index_name', true, ['unique', 'some_column']),
            ],
            $this->mapper->getPrimaryTable()->getIndexes()
        );
    }
}
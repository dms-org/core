<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping;

use Dms\Core\Persistence\Db\Mapping\CustomOrm;
use Dms\Core\Persistence\Db\Mapping\IEntityMapper;
use Dms\Core\Persistence\Db\Schema\Index;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\InlineConstraints\ConstrainedEntity;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\InlineConstraints\InlineConstraintsEntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class InlineConstraintsEntityMapperTest extends DbIntegrationTest
{
    /**
     * @inheritDoc
     */
    protected function loadOrm()
    {
        return CustomOrm::from([ConstrainedEntity::class => InlineConstraintsEntityMapper::class]);
    }

    public function testLoadsCorrectIndexes()
    {
        $this->assertEquals(
                [
                        'index_name'            => new Index('index_name', false, ['indexed']),
                        'unique_index_name'     => new Index('unique_index_name', true, ['unique']),
                        // Using default naming convention
                        'default_index'         => new Index('default_index', false, ['default']),
                        'default2_unique_index' => new Index('default2_unique_index', true, ['default2']),
                ],
                $this->mapper->getPrimaryTable()->getIndexes()
        );
    }
}
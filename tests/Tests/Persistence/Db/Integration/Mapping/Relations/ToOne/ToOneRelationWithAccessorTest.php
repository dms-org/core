<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\ToOne;

use Dms\Core\Persistence\Db\Schema\ForeignKeyMode;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToOneRelation\ParentEntityMapperWithRelationAccessor;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ToOneRelationWithAccessorTest extends ToOneRelationTestBase
{
    /**
     * @inheritDoc
     */
    protected function loadOrm()
    {
        return ParentEntityMapperWithRelationAccessor::orm();
    }

    /**
     * @inheritDoc
     */
    protected function deleteForeignKeyMode()
    {
        return ForeignKeyMode::SET_NULL;
    }
}
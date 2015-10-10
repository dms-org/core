<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\ToOne;

use Iddigital\Cms\Core\Persistence\Db\Schema\ForeignKeyMode;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToOneRelation\ParentEntityMapperWithRelationAccessor;

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
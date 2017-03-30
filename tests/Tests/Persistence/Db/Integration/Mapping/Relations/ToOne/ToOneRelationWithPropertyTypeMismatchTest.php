<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\ToOne;

use Dms\Core\Persistence\Db\Schema\ForeignKeyMode;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToOneRelation\ParentEntityMapperWithIgnoringPropertyType;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToOneRelation\ParentEntityWithMixedProperty;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToOneRelation\SubEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ToOneRelationWithPropertyTypeMismatchTest extends ToOneRelationTestBase
{
    protected function buildTestEntity($id = null, $subVal = 123, $subId = null)
    {
        $entity        = new ParentEntityWithMixedProperty($id);
        $entity->child = new SubEntity($subVal);
        if ($subId !== null) {
            $entity->child->setId($subId);
        }

        return $entity;
    }

    /**
     * @inheritDoc
     */
    protected function loadOrm()
    {
        return ParentEntityMapperWithIgnoringPropertyType::orm();
    }

    /**
     * @inheritDoc
     */
    protected function deleteForeignKeyMode()
    {
        return ForeignKeyMode::SET_NULL;
    }
}
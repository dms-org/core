<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Orm\Fixtures\RelatedId;

use Dms\Core\Persistence\Db\Mapping\Definition\Orm\OrmDefinition;
use Dms\Core\Persistence\Db\Mapping\Orm;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class OneToManyIdOrm extends Orm
{

    /**
     * Defines the object mappers registered in the orm.
     *
     * @param OrmDefinition $orm
     *
     * @return void
     */
    protected function define(OrmDefinition $orm)
    {
        $orm->entity(ParentEntity::class)->from(ParentEntityMapper::class);

        $orm->entity(ChildEntity::class)->from(ChildEntityMapper::class);
    }
}
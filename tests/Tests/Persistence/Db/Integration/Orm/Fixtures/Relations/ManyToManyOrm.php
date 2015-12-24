<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Orm\Fixtures\Relations;

use Dms\Core\Persistence\Db\Mapping\Definition\Orm\OrmDefinition;
use Dms\Core\Persistence\Db\Mapping\Orm;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ManyToManyOrm extends Orm
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
        $orm->entity(OneEntity::class)->from(OneEntityMapper::class);

        $orm->entity(AnotherEntity::class)->from(AnotherEntityMapper::class);
    }
}
<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Locking\Fixtures\IntegerVersion;

use Dms\Core\Persistence\Db\Mapping\CustomOrm;
use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Dms\Core\Persistence\Db\Mapping\EntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class IntegerVersionedEntityMapper extends EntityMapper
{
    public static function orm()
    {
        return CustomOrm::from([
                IntegerVersionedEntity::class => __CLASS__,
        ]);
    }

    /**
     * Defines the entity mapper
     *
     * @param MapperDefinition $map
     *
     * @return void
     */
    protected function define(MapperDefinition $map)
    {
        $map->type(IntegerVersionedEntity::class);
        $map->toTable('data');

        $map->idToPrimaryKey('id');

        $map->property('version')->to('version')->asVersionInteger();
    }
}
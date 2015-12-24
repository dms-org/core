<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Locking\Fixtures\MultiVersion;

use Dms\Core\Persistence\Db\Mapping\CustomOrm;
use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Dms\Core\Persistence\Db\Mapping\EntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MultiVersionedEntityMapper extends EntityMapper
{
    public static function orm()
    {
        return CustomOrm::from([
                MultiVersionedEntity::class => __CLASS__,
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
        $map->type(MultiVersionedEntity::class);
        $map->toTable('data');

        $map->idToPrimaryKey('id');

        $map->property('intVersion')->to('int_version')->asVersionInteger();
        $map->property('dateVersion')->to('date_version')->asVersionDateTime();
    }
}
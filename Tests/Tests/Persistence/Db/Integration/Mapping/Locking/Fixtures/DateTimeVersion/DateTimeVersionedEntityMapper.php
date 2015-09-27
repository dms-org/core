<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Locking\Fixtures\DateTimeVersion;

use Iddigital\Cms\Core\Persistence\Db\Mapping\CustomOrm;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\EntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DateTimeVersionedEntityMapper extends EntityMapper
{
    public static function orm()
    {
        return CustomOrm::from([
                DateTimeVersionedEntity::class => __CLASS__,
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
        $map->type(DateTimeVersionedEntity::class);
        $map->toTable('data');

        $map->idToPrimaryKey('id');

        $map->property('version')->to('version')->asVersionDateTime();
    }
}
<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Locking\Fixtures\EmbeddedVersion;

use Dms\Core\Persistence\Db\Mapping\CustomOrm;
use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Dms\Core\Persistence\Db\Mapping\EntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EntityWithEmbeddedVersionMapper extends EntityMapper
{
    public static function orm()
    {
        return CustomOrm::from([
                EntityWithEmbeddedVersion::class => __CLASS__,
        ], [
                VersionValueObject::class => VersionValueObjectMapper::class,
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
        $map->type(EntityWithEmbeddedVersion::class);
        $map->toTable('data');

        $map->idToPrimaryKey('id');

        $map->embedded('version')->to(VersionValueObject::class);
    }
}
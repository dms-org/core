<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObject\Unique;

use Dms\Core\Persistence\Db\Mapping\CustomOrm;
use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Dms\Core\Persistence\Db\Mapping\EntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EntityWithUniqueValueObjectMapper extends EntityMapper
{
    public static function orm()
    {
        return CustomOrm::from([
                EntityWithUniqueValueObject::class => __CLASS__,
        ]);
    }

    /**
     * Defines the value object mapper
     *
     * @param MapperDefinition $map
     *
     * @return void
     */
    protected function define(MapperDefinition $map)
    {
        $map->type(EntityWithUniqueValueObject::class);
        $map->toTable('entities');

        $map->idToPrimaryKey('id');

        $map->embedded('valueObject')
                ->withColumnsPrefixedBy('unique_')
                ->unique()
                ->using(new EmbeddedUniqueValueObjectMapper());
    }
}
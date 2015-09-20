<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\Types;

use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\EntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TypesMapper extends EntityMapper
{
    /**
     * Defines the entity mapper
     *
     * @param MapperDefinition $map
     *
     * @return void
     */
    protected function define(MapperDefinition $map)
    {
        $map->type(TypesEntity::class);
        $map->toTable('types');

        $map->idToPrimaryKey('id');

        $map->property('null')->to('null')->nullable()->asTinyInt();
        $map->property('int')->to('int')->asInt();
        $map->property('string')->to('string')->asVarchar(500);
        $map->property('bool')->to('bool')->asBool();
        $map->property('float')->to('float')->asDecimal(10, 5);
        $map->property('date')->to('date')->asDate();
        $map->property('time')->to('time')->asTime();
        $map->property('datetime')->to('datetime')->asDateTime();
    }
}
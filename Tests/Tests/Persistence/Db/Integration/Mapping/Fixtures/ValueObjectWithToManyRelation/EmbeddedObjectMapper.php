<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObjectWithToManyRelation;

use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\ValueObjectMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EmbeddedObjectMapper extends ValueObjectMapper
{
    /**
     * Defines the value object mapper
     *
     * @param MapperDefinition $map
     *
     * @return void
     */
    protected function define(MapperDefinition $map)
    {
        $map->type(EmbeddedObject::class);

        $map->relation('children')
            ->to(ChildEntity::class)
            ->toMany()
            ->identifying()
            ->withParentIdAs('parent_id');
    }
}
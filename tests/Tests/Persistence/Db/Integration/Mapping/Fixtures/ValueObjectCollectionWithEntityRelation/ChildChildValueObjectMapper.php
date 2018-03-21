<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObjectCollectionWithEntityRelation;

use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Dms\Core\Persistence\Db\Mapping\IndependentValueObjectMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ChildChildValueObjectMapper extends IndependentValueObjectMapper
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
        $map->type(ChildChildValueObject::class);

        $map->property('data')->to('data')->asVarchar(255);
    }
}
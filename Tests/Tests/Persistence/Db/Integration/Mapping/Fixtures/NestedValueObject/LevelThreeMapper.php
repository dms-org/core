<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\NestedValueObject;

use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IndependentValueObjectMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class LevelThreeMapper extends IndependentValueObjectMapper
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
        $map->type(LevelThree::class);

        $map->property('val')->to('value')->asVarchar(255);
    }
}
<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\NestedValueObject;

use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Dms\Core\Persistence\Db\Mapping\IndependentValueObjectMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class LevelOneMapper extends IndependentValueObjectMapper
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
        $map->type(LevelOne::class);

        $map->embedded('two')->withColumnsPrefixedBy('two_')->using(new LevelTwoMapper());
    }
}
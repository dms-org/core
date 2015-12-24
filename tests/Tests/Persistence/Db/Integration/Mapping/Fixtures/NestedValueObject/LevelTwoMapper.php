<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\NestedValueObject;

use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Dms\Core\Persistence\Db\Mapping\IndependentValueObjectMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class LevelTwoMapper extends IndependentValueObjectMapper
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
        $map->type(LevelTwo::class);

        $map->embedded('three')->withColumnsPrefixedBy('three_')->using(new LevelThreeMapper());
    }
}
<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObject;

use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\ValueObjectMapper;


/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EmbeddedMoneyObjectMapper extends ValueObjectMapper
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
        $map->type(EmbeddedMoneyObject::class);

        $map->property('cents')->to('cents')->asInt();
        $map->enum('currency')->to('currency')->usingValuesFromConstants();
    }
}
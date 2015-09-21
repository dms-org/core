<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObjectCollection;

use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\ValueObjectMapper;


/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EmbeddedEmailAddressMapper extends ValueObjectMapper
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
        $map->type(EmbeddedEmailAddress::class);

        $map->property('email')->to('email')->asVarchar(255);
    }
}
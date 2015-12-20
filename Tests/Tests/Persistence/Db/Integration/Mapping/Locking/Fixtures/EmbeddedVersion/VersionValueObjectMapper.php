<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Locking\Fixtures\EmbeddedVersion;

use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Dms\Core\Persistence\Db\Mapping\ValueObjectMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class VersionValueObjectMapper extends ValueObjectMapper
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
        $map->type(VersionValueObject::class);

        $map->property('number')->to('version')->asVersionInteger();
    }
}
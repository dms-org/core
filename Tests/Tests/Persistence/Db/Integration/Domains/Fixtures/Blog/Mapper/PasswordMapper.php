<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog\Mapper;

use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Dms\Core\Persistence\Db\Mapping\ValueObjectMapper;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog\HashedPassword;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PasswordMapper extends ValueObjectMapper
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
        $map->type(HashedPassword::class);

        $map->property('hash')->to('hash')->asVarchar(255);
        $map->property('algorithm')->to('algorithm')->asVarchar(10);
    }
}
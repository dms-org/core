<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog\Mapper;

use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\EntityMapper;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog\Alias;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class AliasMapper extends EntityMapper
{
    /**
     * AliasMapper constructor.
     */
    public function __construct()
    {
        parent::__construct('aliases');
    }

    /**
     * Defines the entity mapper
     *
     * @param MapperDefinition $map
     *
     * @return void
     */
    protected function define(MapperDefinition $map)
    {
        $map->type(Alias::class);

        $map->idToPrimaryKey('id');
        $map->column('user_id')->asInt();

        $map->property('firstName')->to('first_name')->asVarchar(255);
        $map->property('lastName')->to('last_name')->asVarchar(255);
    }
}
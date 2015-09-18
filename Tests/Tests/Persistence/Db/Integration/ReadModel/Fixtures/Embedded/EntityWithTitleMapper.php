<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\ReadModel\Fixtures\Embedded;

use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\EntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EntityWithTitleMapper extends EntityMapper
{
    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct('entities');
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
        $map->type(EntityWithTitle::class);
        $map->idToPrimaryKey('id');

        $map->property('title')->to('title')->asVarchar(255);
    }
}
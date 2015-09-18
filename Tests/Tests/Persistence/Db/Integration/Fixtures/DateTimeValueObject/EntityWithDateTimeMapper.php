<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\DateTimeValueObject;

use Iddigital\Cms\Core\Persistence\Db\Mapper\DateTimeMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\EntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EntityWithDateTimeMapper extends EntityMapper
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
        $map->type(EntityWithDateTime::class);

        $map->idToPrimaryKey('id');

        $map->embedded('datetime')->using(new DateTimeMapper('datetime'));
    }
}
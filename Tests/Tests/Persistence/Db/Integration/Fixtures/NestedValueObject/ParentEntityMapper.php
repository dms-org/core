<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\NestedValueObject;

use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\EntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ParentEntityMapper extends EntityMapper
{
    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct('parents');
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
        $map->type(ParentEntity::class);

        $map->idToPrimaryKey('id');

        $map->embedded('one')->withColumnsPrefixedBy('one_')->using(new LevelOneMapper());
    }
}
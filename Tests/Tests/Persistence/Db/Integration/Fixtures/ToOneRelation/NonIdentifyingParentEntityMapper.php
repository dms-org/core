<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\ToOneRelation;

use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\EntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class NonIdentifyingParentEntityMapper extends EntityMapper
{
    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct('parent_entities');
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

        $map->relation('child')
                ->using(new SubEntityMapper())
                ->toOne()
                ->withParentIdAs('parent_id');
    }
}
<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\ToManyRelation;

use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\EntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class IdentifyingParentEntityMapper extends EntityMapper
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

        $map->relation('children')
                ->using(new ChildEntityMapper())
                ->toMany()
                ->identifying()
                ->withParentIdAs('parent_id');
    }
}
<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\SelfReferencing\ManyToManyRelation;

use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\EntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class RecursiveEntityMapper extends EntityMapper
{
    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct('recursive_entities');
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
        $map->type(RecursiveEntity::class);

        $map->idToPrimaryKey('id');

        $map->relation('parents')
                ->using($this)
                ->toMany()
                ->throughJoinTable('parents')
                ->withParentIdAs('parent_id')
                ->withRelatedIdAs('child_id');
    }
}
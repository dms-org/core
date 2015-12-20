<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ManyToManyRelation\Bidirectional;

use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Dms\Core\Persistence\Db\Mapping\EntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class AnotherEntityMapper extends EntityMapper
{
    /**
     * Defines the entity mapper
     *
     * @param MapperDefinition $map
     *
     * @return void
     */
    protected function define(MapperDefinition $map)
    {
        $map->type(AnotherEntity::class);
        $map->toTable('anothers');

        $map->idToPrimaryKey('id');

        $map->relation('ones')
                ->to(OneEntity::class)
                ->toMany()
                ->withBidirectionalRelation('others')
                ->throughJoinTable('one_anothers')
                ->withParentIdAs('another_id')
                ->withRelatedIdAs('one_id');
    }
}
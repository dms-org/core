<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Orm\Fixtures\Relations;

use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Dms\Core\Persistence\Db\Mapping\EntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class OneEntityMapper extends EntityMapper
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
        $map->type(OneEntity::class);
        $map->toTable('ones');

        $map->idToPrimaryKey('id');

        $map->relation('others')
                ->to(AnotherEntity::class)
                ->toMany()
                ->withBidirectionalRelation('ones')
                ->throughJoinTable('one_anothers')
                ->withParentIdAs('one_id')
                ->withRelatedIdAs('another_id');
    }
}
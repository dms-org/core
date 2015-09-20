<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Orm\Fixtures\Relations;

use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\EntityMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IOrm;

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
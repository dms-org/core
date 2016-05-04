<?php declare(strict_types = 1);

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ToOneThenMany;

use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Dms\Core\Persistence\Db\Mapping\EntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class SubEntityMapper extends EntityMapper
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
        $map->type(SubEntity::class);
        $map->toTable('subs');

        $map->idToPrimaryKey('id');

        $map->column('parent_id')->nullable()->asInt();

        $map->relation('childIds')
            ->to(ChildEntity::class)
            ->toManyIds()
            ->identifying()
            ->withParentIdAs('sub_id');
    }
}
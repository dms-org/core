<?php declare(strict_types = 1);

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ToOneThenMany;

use Dms\Core\Persistence\Db\Mapping\CustomOrm;
use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Dms\Core\Persistence\Db\Mapping\EntityMapper;

/**
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ParentEntityMapper extends EntityMapper
{
    public static function orm()
    {
        return CustomOrm::from([
            ParentEntity::class => __CLASS__,
            SubEntity::class    => SubEntityMapper::class,
            ChildEntity::class  => ChildEntityMapper::class,
        ]);
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
        $map->toTable('parents');

        $map->idToPrimaryKey('id');

        $map->relation('subEntityId')
            ->to(SubEntity::class)
            ->toOneId()
            ->withParentIdAs('parent_id');
    }
}
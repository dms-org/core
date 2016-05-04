<?php declare(strict_types = 1);

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ToOneThenMany;

use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Dms\Core\Persistence\Db\Mapping\EntityMapper;

/**
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ChildEntityMapper extends EntityMapper
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
        $map->type(ChildEntity::class);
        $map->toTable('children');
        
        $map->idToPrimaryKey('id');

        $map->column('sub_id')->asInt();
    }
}
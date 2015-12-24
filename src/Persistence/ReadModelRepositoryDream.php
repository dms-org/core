<?php

namespace Dms\Core\Persistence;

use Dms\Core\Exception;
use Dms\Core\Persistence\Db\Mapping\ReadModel\Definition\ReadMapperDefinition;

/**
 * An implementation of the read model repository
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
class ReadModelRepositoryDream
{
    protected function define(ReadMapperDefinition $map)
    {
        $map->type(ReadModelClass::class);
        $map->from(new EntityMapper);

        $map->properties(['...' => 'alias']);
        $map->columns(['column' => 'property']);

        $map->relation('categoryId')->to('category')->load(function (ReadMapperDefinition $map) {
            $map->type(CategoryLabel::class);

            $map->properties(['id', 'name']);
        });


    }
}

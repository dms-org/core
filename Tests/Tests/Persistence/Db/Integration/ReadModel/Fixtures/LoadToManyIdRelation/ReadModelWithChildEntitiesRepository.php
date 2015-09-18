<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\ReadModel\Fixtures\LoadToManyIdRelation;

use Iddigital\Cms\Core\Persistence\Db\Mapping\ReadModel\Definition\ReadMapperDefinition;
use Iddigital\Cms\Core\Persistence\ReadModelRepository;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\ToManyIdRelation\ParentEntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ReadModelWithChildEntitiesRepository extends ReadModelRepository
{
    /**
     * Defines the structure of the read model.
     *
     * @param ReadMapperDefinition $map
     *
     * @return void
     */
    protected function define(ReadMapperDefinition $map)
    {
        $map->type(ReadModelWithChildEntities::class);
        $map->from(new ParentEntityMapper());

        $map->relation('childIds')->to('children')->asEntity();
    }
}
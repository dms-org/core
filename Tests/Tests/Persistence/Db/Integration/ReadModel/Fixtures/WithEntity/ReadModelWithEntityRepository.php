<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\ReadModel\Fixtures\WithEntity;

use Iddigital\Cms\Core\Persistence\Db\Mapping\ReadModel\Definition\ReadMapperDefinition;
use Iddigital\Cms\Core\Persistence\ReadModelRepository;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\ToOneIdRelation\ParentEntityMapper;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\ToOneIdRelation\SubEntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ReadModelWithEntityRepository extends ReadModelRepository
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
        $map->type(ReadModelWithEntity::class);
        $map->from(new ParentEntityMapper());

        $map->entityTo('parent');

        $map->relation('childId')->to('child')->asEntity();
    }
}
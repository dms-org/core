<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\ManyToManyRelation;

use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\EntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class OneEntityMapper extends EntityMapper
{
    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct('ones');
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
        $map->type(OneEntity::class);

        $map->idToPrimaryKey('id');

        $map->relation('others')
            ->using(new AnotherEntityMapper())
            ->toMany()
            ->throughJoinTable('one_anothers')
            ->withParentIdAs('one_id')
            ->withRelatedIdAs('another_id');
    }
}
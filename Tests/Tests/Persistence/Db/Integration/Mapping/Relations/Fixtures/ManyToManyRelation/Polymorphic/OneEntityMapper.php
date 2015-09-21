<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ManyToManyRelation\Polymorphic;

use Iddigital\Cms\Core\Persistence\Db\Mapping\CustomOrm;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\EntityMapper;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ManyToManyRelation\OneEntity;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ManyToManyRelation\AnotherEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class OneEntityMapper extends EntityMapper
{
    public static function orm()
    {
        return CustomOrm::from([
                OneEntity::class     => __CLASS__,
                AnotherEntity::class => AnotherEntityMapper::class,
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
        $map->type(OneEntity::class);
        $map->toTable('ones');

        $map->idToPrimaryKey('id');

        $map->relation('others')
            ->to(AnotherEntity::class)
            ->toMany()
            ->throughJoinTable('one_anothers')
            ->withParentIdAs('one_id')
            ->withRelatedIdAs('another_id');
    }
}
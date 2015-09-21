<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObject;

use Iddigital\Cms\Core\Persistence\Db\Mapping\CustomOrm;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\EntityMapper;


/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EntityWithValueObjectMapper extends EntityMapper
{
    public static function orm()
    {
        return CustomOrm::from([
                EntityWithValueObject::class => __CLASS__,
        ], [
                EmbeddedMoneyObject::class => EmbeddedMoneyObjectMapper::class,
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
        $map->type(EntityWithValueObject::class);
        $map->toTable('entities');

        $map->idToPrimaryKey('id');

        $map->property('name')->to('name')->asVarchar(255);
        $map->embedded('money')->to(EmbeddedMoneyObject::class);
        $map->embedded('prefixedMoney')->withColumnsPrefixedBy('prefix_')->to(EmbeddedMoneyObject::class);

        $map->embedded('nullableMoney')
                ->withColumnsPrefixedBy('nullable_')
                ->withIssetColumn('has_nullable_money')
                ->to(EmbeddedMoneyObject::class);
    }
}
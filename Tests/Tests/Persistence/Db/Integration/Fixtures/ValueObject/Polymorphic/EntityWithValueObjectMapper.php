<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\ValueObject\Polymorphic;

use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\EntityMapper;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\ValueObject\EntityWithValueObject;


/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EntityWithValueObjectMapper extends EntityMapper
{
    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct('entities');
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

        $map->idToPrimaryKey('id');

        $map->property('name')->to('name')->asVarchar(255);
        $map->embedded('money')->using(new EmbeddedMoneyObjectMapper());
        $map->embedded('prefixedMoney')->withColumnsPrefixedBy('prefix_')->using(new EmbeddedMoneyObjectMapper());

        $map->embedded('nullableMoney')
                ->withColumnsPrefixedBy('nullable_')
                ->withIssetColumn('has_nullable_money')
                ->using(new EmbeddedMoneyObjectMapper());
    }
}
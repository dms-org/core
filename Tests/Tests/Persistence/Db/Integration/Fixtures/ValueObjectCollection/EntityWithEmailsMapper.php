<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\ValueObjectCollection;

use Iddigital\Cms\Core\Persistence\Db\Mapping\CustomOrm;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\EntityMapper;


/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EntityWithEmailsMapper extends EntityMapper
{
    public static function orm()
    {
        return CustomOrm::from([
                EntityWithEmails::class => __CLASS__
        ], [
                EmbeddedEmailAddress::class => EmbeddedEmailAddressMapper::class
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
        $map->type(EntityWithEmails::class);
        $map->toTable('entities');

        $map->idToPrimaryKey('id');

        $map->embeddedCollection('emails')
                ->toTable('emails')
                ->withPrimaryKey('id')
                ->withForeignKeyToParentAs('entity_id')
                ->to(EmbeddedEmailAddress::class);
    }
}
<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\ValueObjectCollection;

use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\EntityMapper;


/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EntityWithEmailsMapper extends EntityMapper
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
        $map->type(EntityWithEmails::class);

        $map->idToPrimaryKey('id');

        $map->embeddedCollection('emails')
                ->toTable('emails')
                ->withPrimaryKey('id')
                ->withForeignKeyToParentAs('entity_id')
                ->using(new EmbeddedEmailAddressMapper());
    }
}
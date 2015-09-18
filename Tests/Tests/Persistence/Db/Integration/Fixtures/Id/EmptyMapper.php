<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\Id;

use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\EntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EmptyMapper extends EntityMapper
{
    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct('data');
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
        $map->type(EmptyEntity::class);

        $map->idToPrimaryKey('id');
    }
}
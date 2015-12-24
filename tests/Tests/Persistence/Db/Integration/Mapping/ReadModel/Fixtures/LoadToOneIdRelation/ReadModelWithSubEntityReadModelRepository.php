<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel\Fixtures\LoadToOneIdRelation;

use Dms\Core\Persistence\Db\Connection\IConnection;
use Dms\Core\Persistence\Db\Mapping\ReadModel\Definition\ReadMapperDefinition;
use Dms\Core\Persistence\ReadModelRepository;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToOneIdRelation\ParentEntity;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToOneIdRelation\ParentEntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ReadModelWithSubEntityReadModelRepository extends ReadModelRepository
{
    /**
     * @inheritDoc
     */
    public function __construct(IConnection $connection)
    {
        parent::__construct($connection, ParentEntityMapper::orm());
    }

    /**
     * Defines the structure of the read model.
     *
     * @param ReadMapperDefinition $map
     *
     * @return void
     */
    protected function define(ReadMapperDefinition $map)
    {
        $map->type(ReadModelWithSubEntityReadModel::class);
        $map->fromType(ParentEntity::class);

        $map->relation('childId')->to('subEntity')->load(function (ReadMapperDefinition $map) {
            $map->type(SubEntityReadModel::class);

            $map->properties(['id', 'val']);
        });
    }
}
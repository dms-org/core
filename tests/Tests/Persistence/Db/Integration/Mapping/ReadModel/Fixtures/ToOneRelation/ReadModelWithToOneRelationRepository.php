<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel\Fixtures\ToOneRelation;

use Dms\Core\Persistence\Db\Connection\IConnection;
use Dms\Core\Persistence\Db\Mapping\ReadModel\Definition\ReadMapperDefinition;
use Dms\Core\Persistence\ReadModelRepository;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToOneRelation\ParentEntity;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToOneRelation\IdentifyingParentEntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ReadModelWithToOneRelationRepository extends ReadModelRepository
{
    /**
     * @inheritDoc
     */
    public function __construct(IConnection $connection)
    {
        parent::__construct($connection, IdentifyingParentEntityMapper::orm());
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
        $map->type(ReadModelWithToOneRelation::class);
        $map->fromType(ParentEntity::class);

        $map->properties(['child' => 'subEntity']);
    }
}
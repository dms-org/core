<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel\Fixtures\ValueObjectCollection;

use Dms\Core\Persistence\Db\Connection\IConnection;
use Dms\Core\Persistence\Db\Mapping\ReadModel\Definition\ReadMapperDefinition;
use Dms\Core\Persistence\ReadModelRepository;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObjectCollection\EntityWithEmails;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObjectCollection\EntityWithEmailsMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ReadModelWithValueObjectCollectionRepository extends ReadModelRepository
{
    /**
     * @inheritDoc
     */
    public function __construct(IConnection $connection)
    {
        parent::__construct($connection, EntityWithEmailsMapper::orm());
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
        $map->type(ReadModelWithValueObjectCollection::class);
        $map->fromType(EntityWithEmails::class);

        $map->properties(['emails' => 'emails']);
    }
}
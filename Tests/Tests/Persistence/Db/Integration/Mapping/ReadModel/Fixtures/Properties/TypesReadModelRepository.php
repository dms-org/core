<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel\Fixtures\Properties;

use Iddigital\Cms\Core\Persistence\Db\Connection\IConnection;
use Iddigital\Cms\Core\Persistence\Db\Mapping\CustomOrm;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IOrm;
use Iddigital\Cms\Core\Persistence\Db\Mapping\ReadModel\Definition\ReadMapperDefinition;
use Iddigital\Cms\Core\Persistence\ReadModelRepository;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\Types\TypesEntity;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\Types\TypesMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TypesReadModelRepository extends ReadModelRepository
{
    /**
     * @inheritDoc
     */
    public function __construct(IConnection $connection)
    {
        parent::__construct($connection, CustomOrm::from([
            TypesEntity::class => TypesMapper::class
        ]));
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
        $map->type(TypesReadModel::class);
        $map->fromType(TypesEntity::class);

        $map->properties([
                'int',
                'float',
                'date'
        ]);
    }
}
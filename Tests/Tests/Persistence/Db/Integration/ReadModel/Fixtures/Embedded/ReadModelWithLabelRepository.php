<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\ReadModel\Fixtures\Embedded;

use Iddigital\Cms\Core\Persistence\Db\Mapping\ReadModel\Definition\ReadMapperDefinition;
use Iddigital\Cms\Core\Persistence\ReadModelRepository;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ReadModelWithLabelRepository extends ReadModelRepository
{

    /**
     * Defines the structure of the read model.
     *
     * @param ReadMapperDefinition $map
     *
     * @return void
     */
    protected function define(ReadMapperDefinition $map)
    {
        $map->type(ReadModelWithLabel::class);
        $map->from(new EntityWithTitleMapper());

        $map->embedded(new GenericLabelReadModelMapper('title'))->to('label');
    }
}
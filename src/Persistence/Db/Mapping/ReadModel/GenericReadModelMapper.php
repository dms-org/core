<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\ReadModel;

use Dms\Core\Persistence\Db\Mapping\IObjectMapper;
use Dms\Core\Persistence\Db\Mapping\ReadModel\Definition\ReadMapperDefinition;

/**
 * The generic read model mapper class.
 *
 * Simply accepts a callable to define the mapper instead of
 * requiring a separate class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class GenericReadModelMapper
{
    /**
     * Defines the read model mapping.
     *
     * @param ReadMapperDefinition $map
     *
     * @return void
     */
    abstract protected function define(ReadMapperDefinition $map);

    /**
     * Constructs a read model mapper for the supplied entity mapper.
     *
     * @param IObjectMapper $sourceObjectMapper
     *
     * @return ReadModelMapper
     */
    final public function loadMapperFor(IObjectMapper $sourceObjectMapper) : ReadModelMapper
    {
        $map = new ReadMapperDefinition($sourceObjectMapper->getDefinition()->getOrm());
        $map->from($sourceObjectMapper);

        $this->define($map);

        return new ReadModelMapper($map);
    }
}
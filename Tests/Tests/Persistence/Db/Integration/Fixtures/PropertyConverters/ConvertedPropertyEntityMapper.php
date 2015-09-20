<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\PropertyConverters;

use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\EntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ConvertedPropertyEntityMapper extends EntityMapper
{
    /**
     * Defines the entity mapper
     *
     * @param MapperDefinition $map
     *
     * @return void
     */
    protected function define(MapperDefinition $map)
    {
        $map->type(ConvertedPropertyEntity::class);
        $map->toTable('converted_properties');

        $map->idToPrimaryKey('id');

        $map->property('integer')
                ->mappedVia(
                        function ($phpValue, array $properties) {
                            return 'integer:' . (string)$phpValue;
                        },
                        function ($dbValue, array $row) {
                            return (int)substr($dbValue, strlen('integer:'));
                        }
                )
                ->to('varchar')
                ->asVarchar(20);
    }
}
<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObject\Polymorphic;

use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObject\EmbeddedMoneyObjectMapper as BaseEmbeddedMoneyObjectMapper;


/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EmbeddedMoneyObjectMapper extends BaseEmbeddedMoneyObjectMapper
{

    /**
     * Defines the value object mapper
     *
     * @param MapperDefinition $map
     *
     * @return void
     */
    protected function define(MapperDefinition $map)
    {
        $map->column('type')->nullable()->asEnum(['subclass']);

        parent::define($map);

        $map->subclass()->withTypeInColumn('type', 'subclass')->define(function (MapperDefinition $map) {
            $map->type(EmbeddedMoneyObjectSubClass::class);

            $map->property('extra')->to('extra')->asVarchar(255);
        });
    }
}
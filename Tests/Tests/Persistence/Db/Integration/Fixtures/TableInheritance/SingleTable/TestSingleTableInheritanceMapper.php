<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\TableInheritance\SingleTable;

use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\EntityMapper;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\TableInheritance\TestSubclassEntity1;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\TableInheritance\TestSubclassEntity2;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\TableInheritance\TestSubclassEntity3;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\TableInheritance\TestSuperclassEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TestSingleTableInheritanceMapper extends EntityMapper
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
        $map->type(TestSuperclassEntity::class);

        $map->idToPrimaryKey('id');

        $map->column('class_type')->asEnum(['subclass1', 'subclass2', 'subclass3']);

        $map->property('baseProp')->to('base_prop')->asVarchar(255);

        $map->subclass()->withTypeInColumn('class_type', 'subclass1')->define(function (MapperDefinition $map) {
            $map->type(TestSubclassEntity1::class);

            $map->property('subClass1Prop')->to('subclass1_prop')->asInt();

            $map->subclass()->withTypeInColumn('class_type', 'subclass3')->define(function (MapperDefinition $map) {
                $map->type(TestSubclassEntity3::class);

                $map->property('subClass3Prop')->to('subclass3_prop')->asVarchar(255);
            });
        });

        $map->subclass()->withTypeInColumn('class_type', 'subclass2')->define(function (MapperDefinition $map) {
            $map->type(TestSubclassEntity2::class);

            $map->property('subClass2Prop')->to('subclass2_prop')->asInt();
            $map->property('subClass2Prop2')->to('subclass2_prop2')->asBool();
        });
    }
}
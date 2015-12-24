<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Cars\Mapper;

use Dms\Core\Persistence\Db\Mapping\CustomOrm;
use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Dms\Core\Persistence\Db\Mapping\EntityMapper;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Cars\Car;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Cars\ConvertibleCar;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Cars\FamilyCar;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Cars\HatchCar;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Cars\SedanCar;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CarMapper extends EntityMapper
{
    public static function orm()
    {
        return CustomOrm::from([
            Car::class => __CLASS__
        ]);
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
        $map->type(Car::class);
        $map->toTable('cars');

        $map->idToPrimaryKey('id');
        $map->property('brand')->to('brand')->asVarchar(255);

        $map->column('type')->asEnum(['sedan', 'hatch', 'convertible', 'family']);

        $map->subclass()->withTypeInColumn('type', 'sedan')->asType(SedanCar::class);
        $map->subclass()->withTypeInColumn('type', 'hatch')->asType(HatchCar::class);
        $map->subclass()->withTypeInColumn('type', 'convertible')->asType(ConvertibleCar::class);
        $map->subclass()->withTypeInColumn('type', 'family')->asType(FamilyCar::class);
    }
}
<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Cars\Mapper;

use Iddigital\Cms\Core\Persistence\Db\Mapping\CustomOrm;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\EntityMapper;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Cars\Car;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Cars\ConvertibleCar;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Cars\FamilyCar;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Cars\HatchCar;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Cars\SedanCar;

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
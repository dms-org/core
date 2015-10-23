<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Sport\Mapper;

use Iddigital\Cms\Core\Persistence\Db\Mapping\CustomOrm;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\EntityMapper;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Sport\Bowler;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Sport\Cricketer;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Sport\Footballer;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Sport\Player;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ClassTableInheritancePlayerMapper extends EntityMapper
{
    public static function orm()
    {
        return CustomOrm::from([
                Player::class => __CLASS__
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
        $map->type(Player::class);
        $map->toTable('players');

        $map->idToPrimaryKey('id');
        $map->property('name')->to('name')->asVarchar(255);

        $map->subclass()->asSeparateTable('footballers')->define(function (MapperDefinition $map) {
            $map->type(Footballer::class);
            $map->property('club')->to('club')->asVarchar(255);
        });

        $map->subclass()->asSeparateTable('cricketers')->define(function (MapperDefinition $map) {
            $map->type(Cricketer::class);
            $map->property('battingAverage')->to('batting_average')->asInt();

            $map->subclass()->asSeparateTable('bowlers')->define(function (MapperDefinition $map) {
                $map->type(Bowler::class);
                $map->property('bowlingAverage')->to('bowling_average')->asInt();
            });
        });
    }
}
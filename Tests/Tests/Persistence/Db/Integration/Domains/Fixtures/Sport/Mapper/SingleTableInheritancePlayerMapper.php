<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Sport\Mapper;

use Dms\Core\Persistence\Db\Mapping\CustomOrm;
use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Dms\Core\Persistence\Db\Mapping\EntityMapper;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Sport\Bowler;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Sport\Cricketer;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Sport\Footballer;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Sport\Player;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class SingleTableInheritancePlayerMapper extends EntityMapper
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

        $map->column('type')->asEnum(['footballer', 'cricketer', 'bowler']);
        $map->property('name')->to('name')->asVarchar(255);

        $map->subclass()->withTypeInColumn('type', 'footballer')->define(function (MapperDefinition $map) {
            $map->type(Footballer::class);
            $map->property('club')->to('club')->asVarchar(255);
        });

        $map->subclass()->withTypeInColumn('type', 'cricketer')->define(function (MapperDefinition $map) {
            $map->type(Cricketer::class);
            $map->property('battingAverage')->to('batting_average')->asInt();

            $map->subclass()->withTypeInColumn('type', 'bowler')->define(function (MapperDefinition $map) {
                $map->type(Bowler::class);
                $map->property('bowlingAverage')->to('bowling_average')->asInt();
            });
        });
    }
}
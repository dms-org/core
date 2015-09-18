<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapper;

use Iddigital\Cms\Core\Model\Object\Type\Time;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;

/**
 * The time value object mapper
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TimeMapper extends SimpleValueObjectMapper
{
    /**
     * @var string
     */
    protected $columnName;

    public function __construct($columnName)
    {
        $this->columnName = $columnName;
        parent::__construct();
    }

    /**
     * Defines the value object mapper
     *
     * @param MapperDefinition $map
     *
     * @return void
     */
    protected function define(MapperDefinition $map)
    {
        $map->type(Time::class);

        $map->property('dateTime')->to($this->columnName)->asTime();
    }
}
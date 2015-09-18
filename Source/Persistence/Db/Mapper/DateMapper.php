<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapper;

use Iddigital\Cms\Core\Model\Object\Type\Date;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;

/**
 * The date value object mapper
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DateMapper extends SimpleValueObjectMapper
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
        $map->type(Date::class);

        $map->property('dateTime')->to($this->columnName)->asDate();
    }
}
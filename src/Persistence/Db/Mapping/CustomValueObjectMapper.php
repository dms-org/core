<?php

namespace Dms\Core\Persistence\Db\Mapping;

use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;

/**
 * The custom value object mapper base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CustomValueObjectMapper extends ValueObjectMapper
{
    /**
     * @var callable
     */
    protected $defineCallback;

    /**
     * CustomValueObjectMapper constructor.
     *
     * @param IOrm          $orm
     * @param IObjectMapper $parentMapper
     * @param callable      $defineCallback
     */
    public function __construct(IOrm $orm, IObjectMapper $parentMapper, callable $defineCallback)
    {
        $this->defineCallback = $defineCallback;
        parent::__construct($orm, $parentMapper);
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
        call_user_func($this->defineCallback, $map);
    }
}
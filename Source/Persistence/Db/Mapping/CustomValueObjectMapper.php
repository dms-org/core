<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping;

use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;

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
     * @param callable $defineCallback
     */
    public function __construct(callable $defineCallback)
    {
        parent::__construct();
        $this->defineCallback = $defineCallback;
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
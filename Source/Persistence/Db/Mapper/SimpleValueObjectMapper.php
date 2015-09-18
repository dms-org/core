<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapper;

use Iddigital\Cms\Core\Persistence\Db\Mapping\ValueObjectMapper;

/**
 * The simple value object mapper base class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class SimpleValueObjectMapper extends ValueObjectMapper
{
    public function __construct()
    {
        parent::__construct();
    }
}
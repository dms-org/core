<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Definition;

use Iddigital\Cms\Core\Model\Object\FinalizedClassDefinition;

/**
 * The mapper definition base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class MapperDefinitionBase
{
    /**
     * @var FinalizedClassDefinition
     */
    protected $class;
}
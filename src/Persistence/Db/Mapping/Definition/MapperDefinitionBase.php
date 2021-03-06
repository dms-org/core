<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Definition;

use Dms\Core\Model\Object\FinalizedClassDefinition;

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
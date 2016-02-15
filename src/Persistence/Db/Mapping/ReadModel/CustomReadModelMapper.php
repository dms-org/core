<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\ReadModel;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Persistence\Db\Mapping\IOrm;
use Dms\Core\Persistence\Db\Mapping\ReadModel\Definition\ReadMapperDefinition;

/**
 * The custom read model mapper class.
 *
 * Simply accepts a callable to define the mapper instead of
 * requiring a separate class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CustomReadModelMapper extends ReadModelMapper
{
    /**
     * CustomReadModelMapper constructor.
     *
     * @param IOrm     $orm
     * @param callable $defineCallback
     *
     * @throws InvalidArgumentException
     */
    public function __construct(IOrm $orm, callable $defineCallback)
    {
        $definition = new ReadMapperDefinition($orm);
        $defineCallback($definition);

        parent::__construct($definition);
    }
}
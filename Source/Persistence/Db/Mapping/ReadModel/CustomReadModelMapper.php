<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\ReadModel;

use Iddigital\Cms\Core\Persistence\Db\Mapping\ReadModel\Definition\ReadMapperDefinition;

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
     * @param callable $defineCallback
     */
    public function __construct(callable $defineCallback)
    {
        $definition = new ReadMapperDefinition();
        $defineCallback($definition);

        parent::__construct($definition);
    }
}
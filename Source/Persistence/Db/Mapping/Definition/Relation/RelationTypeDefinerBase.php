<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\Relation;

/**
 * The relation type definer class base.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class RelationTypeDefinerBase
{
    /**
     * @var callable
     */
    protected $callback;

    /**
     * @var callable
     */
    protected $mapperLoader;

    /**
     * @var bool
     */
    protected $loadIds;

    public function __construct(callable $callback, callable $mapperLoader, $loadId = false)
    {
        $this->callback     = $callback;
        $this->mapperLoader = $mapperLoader;
        $this->loadIds      = $loadId;
    }
}
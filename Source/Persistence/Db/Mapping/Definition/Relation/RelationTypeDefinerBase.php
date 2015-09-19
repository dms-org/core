<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\Relation;

use Iddigital\Cms\Core\Persistence\Db\Mapping\IEntityMapper;

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
     * @var IEntityMapper
     */
    protected $mapper;

    /**
     * @var bool
     */
    protected $loadIds;

    public function __construct(callable $callback, IEntityMapper $mapper, $loadId = false)
    {
        $this->callback = $callback;
        $this->mapper   = $mapper;
        $this->loadIds  = $loadId;
    }
}
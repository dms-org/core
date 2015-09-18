<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Reference;

use Iddigital\Cms\Core\Persistence\Db\Mapping\IEntityMapper;

/**
 * The relation reference base class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class RelationReference implements IRelationReference
{
    /**
     * @var IEntityMapper
     */
    protected $mapper;

    /**
     * RelationReference constructor.
     *
     * @param IEntityMapper $mapper
     */
    public function __construct(IEntityMapper $mapper)
    {
        $this->mapper = $mapper;
    }

    /**
     * {@inheritDoc}
     */
    final public function getMapper()
    {
        return $this->mapper;
    }
}
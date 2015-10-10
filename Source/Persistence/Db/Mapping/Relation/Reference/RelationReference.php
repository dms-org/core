<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Reference;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IEntityMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\IRelation;

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
     * @var null|string
     */
    private $bidirectionalRelationProperty;

    /**
     * RelationReference constructor.
     *
     * @param IEntityMapper $mapper
     * @param string|null   $bidirectionalRelationProperty
     */
    public function __construct(IEntityMapper $mapper, $bidirectionalRelationProperty = null)
    {
        $this->mapper                        = $mapper;
        $this->bidirectionalRelationProperty = $bidirectionalRelationProperty;
    }

    /**
     * {@inheritDoc}
     */
    final public function getMapper()
    {
        return $this->mapper;
    }

    /**
     * @return IRelation|null
     * @throws InvalidArgumentException
     */
    final public function getBidirectionalRelation()
    {
        if (!$this->bidirectionalRelationProperty) {
            return null;
        }

        return $this->mapper->getDefinition()->getRelationMappedToProperty($this->bidirectionalRelationProperty);
    }
}
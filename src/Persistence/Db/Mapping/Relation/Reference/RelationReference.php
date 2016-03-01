<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Relation\Reference;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Persistence\Db\Mapping\IEntityMapper;
use Dms\Core\Persistence\Db\Mapping\Relation\IRelation;

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
     * @var string|null
     */
    protected $bidirectionalRelationProperty;

    /**
     * RelationReference constructor.
     *
     * @param IEntityMapper $mapper
     * @param string|null   $bidirectionalRelationProperty
     */
    public function __construct(IEntityMapper $mapper, string $bidirectionalRelationProperty = null)
    {
        $this->mapper                        = $mapper;
        $this->bidirectionalRelationProperty = $bidirectionalRelationProperty;
    }

    /**
     * {@inheritDoc}
     */
    final public function getMapper() : IEntityMapper
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
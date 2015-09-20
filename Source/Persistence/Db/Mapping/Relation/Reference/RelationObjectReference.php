<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Reference;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Model\IEntity;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IEntityMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\IRelation;
use Iddigital\Cms\Core\Persistence\Db\PersistenceContext;
use Iddigital\Cms\Core\Persistence\Db\Query\Select;
use Iddigital\Cms\Core\Persistence\Db\Row;

/**
 * The relation object reference base class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class RelationObjectReference extends RelationReference
{
    /**
     * ToOneRelationObjectReference constructor.
     *
     * @param IEntityMapper $mapper
     * @param string|null   $bidirectionalRelationProperty
     *
     * @throws InvalidArgumentException
     */
    public function __construct(IEntityMapper $mapper, $bidirectionalRelationProperty = null)
    {
        parent::__construct($mapper, $bidirectionalRelationProperty);
    }

    /**
     * @return Select
     */
    public function getSelect()
    {
        return $this->mapper->getSelect();
    }

    /**
     * @inheritDoc
     */
    public function getIdFromValue($childValue)
    {
        if ($childValue === null) {
            return null;
        }

        if (!($childValue instanceof IEntity)) {
            throw InvalidArgumentException::format(
                    'Invalid child value: expecting instance of %s, %s given',
                    $this->mapper->getObjectType(), gettype($childValue)
            );
        }

        return $childValue->getId();
    }

    /**
     * @param PersistenceContext $context
     * @param array              $children
     *
     * @return Row[]
     */
    final protected function persistChildrenIgnoringBidirectionalRelation(
            PersistenceContext $context,
            array $children
    ) {
        $bidirectionalRelation = $this->getBidirectionalRelation();

        return $context->ignoreRelationsFor(
                function () use ($context, $children) {
                    return $this->mapper->persistAll($context, array_filter($children));
                },
                $bidirectionalRelation ? [$bidirectionalRelation] : []
        );
    }
}
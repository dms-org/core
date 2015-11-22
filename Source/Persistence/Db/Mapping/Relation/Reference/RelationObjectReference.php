<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Reference;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Model\IEntity;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Hook\IPersistHook;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IEntityMapper;
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
     * @var string|null
     */
    protected $persistHookIdToIgnore;

    /**
     * RelationObjectReference constructor.
     *
     * @param IEntityMapper $mapper
     * @param string|null   $bidirectionalRelationProperty
     * @param string|null   $persistHookIdToIgnore
     *
     * @throws InvalidArgumentException
     */
    public function __construct(IEntityMapper $mapper, $bidirectionalRelationProperty = null, $persistHookIdToIgnore = null)
    {
        parent::__construct($mapper, $bidirectionalRelationProperty);

        $this->persistHookIdToIgnore = $persistHookIdToIgnore;
    }

    /**
     * @param Select $select
     * @param string $relatedTableAlias
     *
     * @return void
     */
    public function addLoadToSelect(Select $select, $relatedTableAlias)
    {
        $this->mapper->getMapping()->addLoadToSelect($select, $relatedTableAlias);
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
        $persistHook           = $this->getPersistHook();

        return $context->ignoreRelationsFor(
                function () use ($context, $children, $persistHook) {
                    return $context->ignorePersistHooksFor(function () use ($context, $children) {

                        return $this->mapper->persistAll($context, array_filter($children));

                    }, $persistHook ? [$persistHook] : []);
                },
                $bidirectionalRelation ? [$bidirectionalRelation] : []
        );
    }

    /**
     * @return IPersistHook|null
     */
    final public function getPersistHook()
    {
        if (!$this->persistHookIdToIgnore) {
            return null;
        }

        return $this->mapper->getDefinition()->getPersistHook($this->persistHookIdToIgnore);
    }
}
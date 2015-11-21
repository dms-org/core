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
     * @var IPersistHook|null
     */
    protected $persistHookToIgnore;

    /**
     * RelationObjectReference constructor.
     *
     * @param IEntityMapper     $mapper
     * @param string|null       $bidirectionalRelationProperty
     * @param IPersistHook|null $persistHookToIgnore
     *
     * @throws InvalidArgumentException
     */
    public function __construct(IEntityMapper $mapper, $bidirectionalRelationProperty = null, IPersistHook $persistHookToIgnore = null)
    {
        parent::__construct($mapper, $bidirectionalRelationProperty);

        // TODO: Reference the persist hook by some sort of scalar id
        // because if the hook ever cloned via ::withColumnsPrefixedBy
        // this reference will no longer be valid and hence not be ignored
        // when persisting
        $this->persistHookToIgnore = $persistHookToIgnore;
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
                    return $context->ignorePersistHooksFor(function () use ($context, $children) {

                        return $this->mapper->persistAll($context, array_filter($children));

                    }, $this->persistHookToIgnore ? [$this->persistHookToIgnore] : []);
                },
                $bidirectionalRelation ? [$bidirectionalRelation] : []
        );
    }
}
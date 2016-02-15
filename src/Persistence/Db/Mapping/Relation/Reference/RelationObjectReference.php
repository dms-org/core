<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Relation\Reference;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Model\IEntity;
use Dms\Core\Persistence\Db\Mapping\Hook\IPersistHook;
use Dms\Core\Persistence\Db\Mapping\IEntityMapper;
use Dms\Core\Persistence\Db\PersistenceContext;
use Dms\Core\Persistence\Db\Query\Select;
use Dms\Core\Persistence\Db\Row;

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
    public function __construct(IEntityMapper $mapper, string $bidirectionalRelationProperty = null, string $persistHookIdToIgnore = null)
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
    public function addLoadToSelect(Select $select, string $relatedTableAlias)
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
    ) : array {
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
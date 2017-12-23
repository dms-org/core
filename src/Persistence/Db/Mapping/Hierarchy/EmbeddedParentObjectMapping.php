<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Hierarchy;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Model\ITypedObject;
use Dms\Core\Persistence\Db\Mapping\Definition\FinalizedMapperDefinition;
use Dms\Core\Persistence\Db\Mapping\IEntityMapper;
use Dms\Core\Persistence\Db\Mapping\NullObjectMapper;
use Dms\Core\Persistence\Db\PersistenceContext;
use Dms\Core\Persistence\Db\Query\Delete;
use Dms\Core\Persistence\Db\Row;

/**
 * The embedded object mapping class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EmbeddedParentObjectMapping extends ParentObjectMapping implements IEmbeddedObjectMapping
{
    /**
     * @var IEntityMapper
     */
    private $rootEntityMapper;

    /**
     * @inheritDoc
     */
    public function __construct(FinalizedMapperDefinition $definition, IEntityMapper $rootEntityMapper = null)
    {
        parent::__construct($definition);

        if ($this->hasEntityRelations() && !$rootEntityMapper) {
            throw InvalidArgumentException::format(
                    'Invalid embedded object definition for class %s: object has relations to other entities and no parent mapper has been specified',
                    $this->definition->getClassName()
            );
        }

        $this->rootEntityMapper     = $rootEntityMapper;
        $this->loadPrimaryKeyColumnName();
    }

    /**
     * @inheritDoc
     */
    public function withEmbeddedColumnsPrefixedBy(string $prefix)
    {
        $clone = parent::withEmbeddedColumnsPrefixedBy($prefix);
        $this->loadPrimaryKeyColumnName();

        return $clone;
    }

    /**
     * @return void
     */
    private function loadPrimaryKeyColumnName()
    {
        if ($this->rootEntityMapper && !($this->rootEntityMapper instanceof NullObjectMapper)) {
            $this->primaryKeyColumnName = $this->rootEntityMapper->getPrimaryTable()->getPrimaryKeyColumnName();
        }
    }

    /**
     * @param PersistenceContext $context
     * @param ITypedObject[]     $objects
     *
     * @return Row[]
     */
    public function persistAllObjects(PersistenceContext $context, array $objects) : array
    {
        $rows = [];

        foreach ($objects as $key => $object) {
            $rows[$key] = new Row($this->table);
        }

        $this->persistAllBeforeParent($context, $objects, $rows);
        $this->persistAll($context, $objects, $rows);
        $this->persistAllAfterParent($context, $objects, $rows);

        return $rows;
    }

    public function persistAllBeforeParent(PersistenceContext $context, array $objects, array $rows)
    {
        $this->performPrePersist($context, $objects, $rows, $this->getObjectProperties($objects));
    }

    /**
     * @inheritDoc
     */
    public function persistAll(
            PersistenceContext $context,
            array $objects,
            array $rows,
            array $extraData = null
    ) {
        $this->persistObjectDataToRows($objects, $rows);
        $this->performLockingOperations($context, $objects, $rows);
    }

    protected function performPersist(PersistenceContext $context, array $rows, array $extraData = null)
    {
        // No need to perform query, will be inserted in parent rows
    }

    public function persistAllAfterParent(PersistenceContext $context, array $objects, array $rows)
    {
        $this->performPostPersist($context, $objects, $rows, $this->getObjectProperties($objects));
    }

    public function deleteBeforeParent(PersistenceContext $context, Delete $deleteQuery)
    {
        $this->performPreDelete($context, $deleteQuery);
    }

    public function delete(PersistenceContext $context, Delete $deleteQuery, $dependencyMode = null)
    {

    }

    protected function performDelete(PersistenceContext $context, Delete $deleteQuery)
    {

    }

    public function deleteAfterParent(PersistenceContext $context, Delete $deleteQuery)
    {
        $this->performPostDelete($context, $deleteQuery);
    }
}
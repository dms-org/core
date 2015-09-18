<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Hierarchy;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\FinalizedMapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IEntityMapper;
use Iddigital\Cms\Core\Persistence\Db\PersistenceContext;
use Iddigital\Cms\Core\Persistence\Db\Query\Delete;

/**
 * The embedded object mapping class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EmbeddedParentObjectMapping extends ParentObjectMapping implements IEmbeddedObjectMapping
{
    /**
     * @var IEntityMapper|null
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

        if ($rootEntityMapper) {
            $this->rootEntityMapper = $rootEntityMapper;
            $rootEntityMapper->onInitialized(function () use ($rootEntityMapper) {
                $this->primaryKeyColumnName = $rootEntityMapper->getPrimaryTable()->getPrimaryKeyColumnName();
            });
        }
    }

    /**
     * @inheritDoc
     */
    public function withEmbeddedColumnsPrefixedBy($prefix)
    {
        $clone = parent::withEmbeddedColumnsPrefixedBy($prefix);

        if ($this->rootEntityMapper) {
            $this->rootEntityMapper->onInitialized(function () use ($clone) {
                $clone->primaryKeyColumnName = $this->rootEntityMapper->getPrimaryTable()->getPrimaryKeyColumnName();
            });
        }

        return $clone;
    }

    public function persistAllBeforeParent(PersistenceContext $context, array $objects, array $rows)
    {
        $this->performPrePersist($context, $objects, $rows, $this->getObjectProperties($objects));
    }

    public function persistAll(PersistenceContext $context, array $objects, array $rows)
    {
        $this->persistObjectDataToRows($objects, $rows);
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

    public function delete(PersistenceContext $context, Delete $deleteQuery)
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
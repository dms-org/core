<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Model\ITypedObject;
use Iddigital\Cms\Core\Persistence\Db\LoadingContext;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\FinalizedMapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Hierarchy\EmbeddedParentObjectMapping;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Hierarchy\ParentObjectMapping;
use Iddigital\Cms\Core\Persistence\Db\PersistenceContext;
use Iddigital\Cms\Core\Persistence\Db\Query\Delete;
use Iddigital\Cms\Core\Persistence\Db\Row;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;

/**
 * The value object mapper base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class ValueObjectMapper extends ObjectMapper implements IEmbeddedObjectMapper
{
    /**
     * @var EmbeddedParentObjectMapping
     */
    protected $mapping;

    /**
     * @var IObjectMapper|null
     */
    private $parentMapper;

    /**
     * @param IOrm               $orm
     * @param IObjectMapper|null $parentMapper
     */
    public function __construct(IOrm $orm, IObjectMapper $parentMapper = null)
    {
        $rootEntityMapper   = $this->getRootEntityMapper();
        $this->parentMapper = $parentMapper;
        $definition         = new MapperDefinition($orm);
        $this->define($definition);

        parent::__construct($definition->finalize($rootEntityMapper ? $rootEntityMapper->getPrimaryTableName() : '__EMBEDDED__'));
    }

    /**
     * @return IObjectMapper
     */
    final public function getParentMapper()
    {
        return $this->parentMapper;
    }

    /**
     * @inheritDoc
     */
    final public function getRootEntityMapper()
    {
        $parentMapper = $this->parentMapper;
        if ($parentMapper instanceof IEntityMapper) {
            return $parentMapper;
        } elseif ($parentMapper instanceof IEmbeddedObjectMapper) {
            return $parentMapper->getRootEntityMapper();
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    final protected function loadMapping(FinalizedMapperDefinition $definition)
    {
        return new EmbeddedParentObjectMapping($definition, $this->getRootEntityMapper());
    }

    /**
     * {@inheritDoc}
     */
    final public function asSeparateTable(Table $table)
    {
        $clone          = clone $this;
        $clone->mapping = new ParentObjectMapping($this->getDefinition()->withTable($table));

        return $clone;
    }

    final protected function loadFromDefinition(FinalizedMapperDefinition $definition)
    {

    }

    /**
     * Defines the value object mapper
     *
     * @param MapperDefinition $map
     *
     * @return void
     */
    abstract protected function define(MapperDefinition $map);

    /**
     * {@inheritDoc}
     */
    final public function withColumnsPrefixedBy($prefix)
    {
        $clone          = clone $this;
        $clone->mapping = $this->mapping->withEmbeddedColumnsPrefixedBy($prefix);

        return $clone;
    }

    /**
     * {@inheritDoc}
     */
    final protected function loadObjectsFromContext(LoadingContext $context, array $rows, array &$loadedObjects, array &$newObjects)
    {
        foreach ($rows as $key => $row) {
            $newObjects[$key] = $this->constructNewObjectsFromRow($context, $row);
        }
    }

    /**
     * {@inheritDoc}
     */
    final public function persistAllToRowsBeforeParent(PersistenceContext $context, array $objects, array $rows)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'objects', $objects, $this->getObjectType());

        if (empty($rows)) {
            return;
        }

        $this->mapping->persistAllBeforeParent($context, $objects, $rows);
    }


    /**
     * {@inheritDoc}
     */
    final public function persistToRow(PersistenceContext $context, ITypedObject $object, Row $row)
    {
        $this->persistAllToRows($context, [0 => $object], [0 => $row]);
    }

    /**
     * {@inheritDoc}
     */
    final public function persistAllToRows(PersistenceContext $context, array $objects, array $rows, $dependencyMode = null)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'objects', $objects, $this->getObjectType());

        if (empty($rows)) {
            return;
        }

        $this->persistObjects($context, $objects, $rows);
    }


    /**
     * {@inheritDoc}
     */
    final public function persistAllToRowsAfterParent(PersistenceContext $context, array $objects, array $rows)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'objects', $objects, $this->getObjectType());

        if (empty($rows)) {
            return;
        }

        $this->mapping->persistAllAfterParent($context, $objects, $rows);
    }

    /**
     * @inheritDoc
     */
    final public function deleteFromQueryBeforeParent(PersistenceContext $context, Delete $deleteQuery)
    {
        $this->mapping->deleteBeforeParent($context, $deleteQuery);
    }

    /**
     * {@inheritDoc}
     */
    final public function deleteFromQueryAfterParent(PersistenceContext $context, Delete $deleteQuery)
    {
        $this->mapping->deleteAfterParent($context, $deleteQuery);
    }
}
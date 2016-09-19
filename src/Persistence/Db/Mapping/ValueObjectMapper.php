<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Model\ITypedObject;
use Dms\Core\Persistence\Db\LoadingContext;
use Dms\Core\Persistence\Db\Mapping\Definition\FinalizedMapperDefinition;
use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Dms\Core\Persistence\Db\Mapping\Hierarchy\EmbeddedParentObjectMapping;
use Dms\Core\Persistence\Db\Mapping\Hierarchy\ParentObjectMapping;
use Dms\Core\Persistence\Db\PersistenceContext;
use Dms\Core\Persistence\Db\Query\Delete;
use Dms\Core\Persistence\Db\Row;
use Dms\Core\Persistence\Db\Schema\Table;

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
     * @var bool
     */
    protected $isSeparateTable = false;

    /**
     * @param IOrm               $orm
     * @param IObjectMapper|null $parentMapper
     */
    public function __construct(IOrm $orm, IObjectMapper $parentMapper = null)
    {
        if ($parentMapper instanceof NullObjectMapper) {
            $parentMapper = null;
        }

        $this->parentMapper = $parentMapper;
        $definition         = new MapperDefinition($orm);
        $this->define($definition);
        $rootEntityMapper = $this->getRootEntityMapper();
        $tableName        = $rootEntityMapper ? $rootEntityMapper->getPrimaryTableName() : '__EMBEDDED__';

        foreach ($orm->getPlugins() as $plugin) {
            $plugin->defineMapper($this, $definition);
        }

        parent::__construct($definition->finalize($tableName));
    }

    /**
     * @return IObjectMapper
     */
    final public function getParentMapper() : IObjectMapper
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
    public function getTableWhichThisIsEmbeddedWithin() : Table
    {
        if ($this->isSeparateTable) {
            return $this->getDefinition()->getTable();
        }

        if ($this->parentMapper instanceof IEmbeddedObjectMapper) {
            return $this->parentMapper->getTableWhichThisIsEmbeddedWithin();
        } elseif ($this->parentMapper instanceof IEntityMapper) {
            return $this->parentMapper->getPrimaryTable();
        }

        return $this->getDefinition()->getTable();
    }

    /**
     * {@inheritDoc}
     */
    final protected function loadMapping(FinalizedMapperDefinition $definition) : Hierarchy\ParentObjectMapping
    {
        return new EmbeddedParentObjectMapping($definition, $this->getRootEntityMapper());
    }

    /**
     * {@inheritDoc}
     */
    public function isSeparateTable() : bool
    {
        return $this->isSeparateTable;
    }

    /**
     * {@inheritDoc}
     */
    final public function asSeparateTable(string $name, array $extraColumns = [], array $extraIndexes = [], array $extraForeignKeys = []) : IEmbeddedObjectMapper
    {
        $table = $this->loadTableWithExtraColumns($name, $extraColumns, $extraIndexes, $extraForeignKeys);

        $clone                  = clone $this;
        $clone->mapping         = new ParentObjectMapping($this->getDefinition()->withTable($table));
        $clone->isSeparateTable = true;

        return $clone;
    }

    private function loadTableWithExtraColumns($name, array $extraColumns, array $extraIndexes, array $extraForeignKeys)
    {
        $table   = $this->getDefinition()->getTable();
        $columns = $table->getColumns();

        return $table
            ->withName($name)
            ->withColumns(array_merge($extraColumns, $columns))
            ->withIndexes(array_merge($extraIndexes, $table->getIndexes()))
            ->withForeignKeys(array_merge($extraForeignKeys, $table->getForeignKeys()));
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
    final public function withColumnsPrefixedBy(string $prefix) : IEmbeddedObjectMapper
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
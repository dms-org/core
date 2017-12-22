<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Hierarchy;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Persistence\Db\Mapping\Definition\FinalizedMapperDefinition;
use Dms\Core\Persistence\Db\Mapping\Relation\IRelation;
use Dms\Core\Persistence\Db\PersistenceContext;
use Dms\Core\Persistence\Db\Query\Delete;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Persistence\Db\Query\Query;
use Dms\Core\Persistence\Db\Query\Select;
use Dms\Core\Persistence\Db\Row;
use Dms\Core\Persistence\Db\Schema\Table;

/**
 * The embedded object mapping class.
 *
 * This uses single table inheritance to map multiple classes to
 * a single table.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EmbeddedSubClassObjectMapping extends SubClassObjectMapping implements IEmbeddedObjectMapping
{
    /**
     * @var string
     */
    protected $classTypeColumnName;

    /**
     * @var mixed
     */
    protected $classTypeValue;

    /**
     * @param Table                     $parentTable
     * @param FinalizedMapperDefinition $definition
     * @param string                    $classTypeColumnName
     * @param mixed                     $classTypeValue
     *
     * @throws InvalidArgumentException
     */
    public function __construct(Table $parentTable, FinalizedMapperDefinition $definition, string $classTypeColumnName, $classTypeValue)
    {
        InvalidArgumentException::verify(is_string($classTypeColumnName), 'class type column name must be a string');

        $this->classTypeColumnName = $classTypeColumnName;
        $this->classTypeValue      = $classTypeValue;

        if (!$parentTable->hasColumn($classTypeColumnName)) {
            throw InvalidArgumentException::format(
                'Invalid class type column name: column %s does not exist on parent table',
                $classTypeColumnName
            );
        }

        parent::__construct($parentTable, $definition, IRelation::DEPENDENT_PARENTS);
    }

    protected function loadRequiredColumns(FinalizedMapperDefinition $definition)
    {
        return [$this->classTypeColumnName];
    }

    protected function addLoadClausesToSelect(Select $select, string $tableAlias) : string
    {
        $table = $select->getTableFromAlias($tableAlias);

        foreach ($this->getAllColumnsToLoad() as $column) {
            // If this column is already loading from a parent table, ignore
            if (isset($select->getAliasColumnMap()[$column])) {
                continue;
            }

            $select->addColumn($column, Expr::column($tableAlias, $table->getColumn($column)));
        }

        return $tableAlias;
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
        /** @var Row[] $rows */
        foreach ($rows as $row) {
            $row->setColumn($this->classTypeColumnName, $this->classTypeValue);
        }

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


    public function delete(PersistenceContext $context, Delete $deleteQuery, $dependencyMode = null)
    {

    }

    protected function performDelete(PersistenceContext $context, Delete $deleteQuery)
    {

    }

    /**
     * @param PersistenceContext $context
     * @param Delete             $deleteQuery
     */
    public function deleteAfterParent(PersistenceContext $context, Delete $deleteQuery)
    {
        $this->performPostDelete($context, $deleteQuery);
    }

    /**
     * {@inheritDoc}
     */
    public function withEmbeddedColumnsPrefixedBy(string $prefix)
    {
        $clone                      = parent::withEmbeddedColumnsPrefixedBy($prefix);
        $clone->classTypeColumnName = $prefix . $clone->classTypeColumnName;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function rowMatchesObjectType(Row $row) : bool
    {
        return $row->getColumn($this->classTypeColumnName) === $this->classTypeValue;
    }

    /**
     * {@inheritdoc}
     */
    public function makeClassConditionExpr(Query $query) : \Dms\Core\Persistence\Db\Query\Expression\Expr
    {
        $parentAlias = $this->parentTable->getName();
        $column      = $this->parentTable->findColumn($this->classTypeColumnName);

        return Expr::equal(
            Expr::column($parentAlias, $column),
            Expr::param($column->getType(), $this->classTypeValue)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function addSpecificLoadToQuery(Query $query, string $objectType, array &$subclassTableAliases = [])
    {
        $parentAlias = $this->parentTable->getName();
        
        if ($query instanceof Select) {
            foreach ($this->specificColumnsToLoad as $columnName) {
                $query->addColumn($columnName, Expr::column($parentAlias, $this->parentTable->getColumn($columnName)));
            }
        }

        foreach ($this->subClassMappings as $mapping) {
            if ($mapping instanceof self && is_a($objectType, $mapping->getObjectType(), true)) {
                $mapping->addSpecificLoadToQuery($query, $objectType, $subclassTableAliases);

                return;
            }
        }

        $column      = $this->parentTable->findColumn($this->classTypeColumnName);

        $subclassTableAliases[$this->getObjectType()] = $parentAlias;

        $query->where(Expr::equal(
            Expr::column($parentAlias, $column),
            Expr::param($column->getType(), $this->classTypeValue)
        ));

        if ($query instanceof Select) {
            $this->addLoadToSelect($query, $parentAlias);
        }

        parent::addSpecificLoadToQuery($query, $objectType, $subclassTableAliases);
    }
}
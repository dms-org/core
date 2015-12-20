<?php

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
    public function __construct(Table $parentTable, FinalizedMapperDefinition $definition, $classTypeColumnName, $classTypeValue)
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


    public function persistAllBeforeParent(PersistenceContext $context, array $objects, array $rows)
    {
        $this->performPrePersist($context, $objects, $rows, $this->getObjectProperties($objects));
    }

    public function persistAll(PersistenceContext $context, array $objects, array $rows)
    {
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


    public function delete(PersistenceContext $context, Delete $deleteQuery)
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
    public function withEmbeddedColumnsPrefixedBy($prefix)
    {
        $clone                      = parent::withEmbeddedColumnsPrefixedBy($prefix);
        $clone->classTypeColumnName = $prefix . $clone->classTypeColumnName;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function rowMatchesObjectType(Row $row)
    {
        return $row->getColumn($this->classTypeColumnName) === $this->classTypeValue;
    }

    /**
     * {@inheritdoc}
     */
    public function makeClassConditionExpr(Query $query)
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
    public function addSpecificLoadToQuery(Query $query, $objectType)
    {
        foreach ($this->subClassMappings as $mapping) {
            if ($mapping instanceof self && is_a($objectType, $mapping->getObjectType(), true)) {
                $mapping->addSpecificLoadToQuery($query, $objectType);
                return;
            }
        }

        $parentAlias = $this->parentTable->getName();
        $column      = $this->parentTable->findColumn($this->classTypeColumnName);

        $query->where(Expr::equal(
                Expr::column($parentAlias, $column),
                Expr::param($column->getType(), $this->classTypeValue)
        ));

        if ($query instanceof Select) {
            $this->addLoadToSelect($query, $parentAlias);
        }

        parent::addSpecificLoadToQuery($query, $objectType);
    }
}
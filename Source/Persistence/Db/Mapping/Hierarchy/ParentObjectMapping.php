<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Hierarchy;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Model\ITypedObject;
use Iddigital\Cms\Core\Persistence\Db\LoadingContext;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\FinalizedMapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\PersistenceContext;
use Iddigital\Cms\Core\Persistence\Db\Query\Delete;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\Expr;
use Iddigital\Cms\Core\Persistence\Db\Query\Query;
use Iddigital\Cms\Core\Persistence\Db\Query\Select;
use Iddigital\Cms\Core\Persistence\Db\Row;
use Iddigital\Cms\Core\Persistence\Db\RowSet;
use Iddigital\Cms\Core\Persistence\Db\Schema\ForeignKey;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;

/**
 * The root object mapping class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ParentObjectMapping extends ObjectMapping
{
    /**
     * @var Table
     */
    protected $table;

    /**
     * @inheritDoc
     */
    public function __construct(FinalizedMapperDefinition $definition)
    {
        parent::__construct($definition, null);

        $this->table = $definition->getTable();
    }

    protected function loadFromDefinition(FinalizedMapperDefinition $definition)
    {
        parent::loadFromDefinition($definition);

        $this->table = $definition->getTable();
    }

    /**
     * @param ForeignKey $foreignKey
     *
     * @return void
     */
    public function addForeignKey(ForeignKey $foreignKey)
    {
        $this->definition->addForeignKey($foreignKey);
    }

    /**
     * {@inheritdoc}
     */
    protected function performPersist(PersistenceContext $context, array $rows, array $extraData = null)
    {
        $context->upsert(new RowSet($this->table, $rows), $this->getLockingColumnNames());
    }

    private function getLockingColumnNames()
    {
        $lockingColumnNames = [];
        foreach ($this->definition->getLockingStrategies() as $lockingStrategy) {
            $lockingColumnNames = array_merge($lockingColumnNames, $lockingStrategy->getLockingColumnNames());
        }

        return array_unique($lockingColumnNames, SORT_STRING);
    }

    /**
     * {@inheritdoc}
     */
    protected function performDelete(PersistenceContext $context, Delete $deleteQuery)
    {
        if ($deleteQuery->getTable()->getName() !== $this->table->getName()) {
            throw InvalidArgumentException::format(
                    'Invalid delete query: expecting for table %s, %s given',
                    $this->table->getName(), $deleteQuery->getTable()->getName()
            );
        }

        $context->queue($deleteQuery);
    }

    /**
     * @param LoadingContext $context
     * @param Row[]          $rows
     *
     * @return ITypedObject[]
     */
    public function loadAllObjects(LoadingContext $context, array $rows)
    {
        $objects = [];

        foreach ($rows as $key => $row) {
            $objects[$key] = $this->constructNewObjectFromRow($row);
        }

        $this->loadAll($context, $objects, $rows);

        return $objects;
    }

    /**
     * @param PersistenceContext $context
     * @param ITypedObject[]     $objects
     *
     * @return Row[]
     */
    public function persistAllObjects(PersistenceContext $context, array $objects)
    {
        $rows = [];

        foreach ($objects as $key => $object) {
            $rows[$key] = new Row($this->table);
        }

        $this->persistAll($context, $objects, $rows);

        return $rows;
    }

    /**
     * {@inheritdoc}
     */
    public function rowMatchesObjectType(Row $row)
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    protected function makeClassConditionExpr(Query $query)
    {
        return Expr::true();
    }

    /**
     * @inheritDoc
     */
    public function addSpecificLoadToQuery(Query $query, $objectType)
    {
        if ($query instanceof Select) {
            foreach ($this->specificColumnsToLoad as $column) {
                $query->addRawColumn($column);
            }
        }

        parent::addSpecificLoadToQuery($query, $objectType);
    }


}
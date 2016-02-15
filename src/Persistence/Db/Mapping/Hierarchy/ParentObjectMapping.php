<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Hierarchy;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Model\ITypedObject;
use Dms\Core\Persistence\Db\LoadingContext;
use Dms\Core\Persistence\Db\Mapping\Definition\FinalizedMapperDefinition;
use Dms\Core\Persistence\Db\Mapping\Hook\IPersistHook;
use Dms\Core\Persistence\Db\Mapping\Relation\IEmbeddedToOneRelation;
use Dms\Core\Persistence\Db\PersistenceContext;
use Dms\Core\Persistence\Db\Query\Delete;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Persistence\Db\Query\Query;
use Dms\Core\Persistence\Db\Query\Select;
use Dms\Core\Persistence\Db\Row;
use Dms\Core\Persistence\Db\RowSet;
use Dms\Core\Persistence\Db\Schema\ForeignKey;
use Dms\Core\Persistence\Db\Schema\Table;

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
     * @param IPersistHook $persistHook
     *
     * @return void
     */
    public function addPersistHook(IPersistHook $persistHook)
    {
        $this->definition->addPersistHook($persistHook);
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

        foreach ($this->definition->getRelationMappings() as $relationMapping) {
            $relation = $relationMapping->getRelation();

            if ($relation instanceof IEmbeddedToOneRelation) {
                $lockingColumnNames = array_merge($lockingColumnNames, $relation->getMapper()->getMapping()->getLockingColumnNames());
            }
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
    public function loadAllObjects(LoadingContext $context, array $rows) : array
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
    public function persistAllObjects(PersistenceContext $context, array $objects) : array
    {
        $rows = [];

        foreach ($objects as $key => $object) {
            $rows[$key] = new Row($this->table);
        }

        $this->persistAll($context, $objects, $rows);

        return $rows;
    }


    /**
     * @param PersistenceContext $context
     * @param ITypedObject       $object
     *
     * @return Row
     */
    public function persistObject(PersistenceContext $context, ITypedObject $object) : \Dms\Core\Persistence\Db\Row
    {
        return $this->persistAllObjects($context, [0 => $object])[0];
    }

    /**
     * {@inheritdoc}
     */
    public function rowMatchesObjectType(Row $row) : bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    protected function makeClassConditionExpr(Query $query) : \Dms\Core\Persistence\Db\Query\Expression\Expr
    {
        return Expr::true();
    }

    /**
     * @inheritDoc
     */
    public function addSpecificLoadToQuery(Query $query, string $objectType)
    {
        if ($query instanceof Select) {
            foreach ($this->specificColumnsToLoad as $column) {
                $query->addRawColumn($column);
            }
        }

        parent::addSpecificLoadToQuery($query, $objectType);
    }


}
<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Hook;

use Dms\Core\Model\ITypedObject;
use Dms\Core\Persistence\Db\PersistenceContext;
use Dms\Core\Persistence\Db\Query\Delete;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Persistence\Db\Query\ResequenceOrderIndexColumn;
use Dms\Core\Persistence\Db\Query\Select;
use Dms\Core\Persistence\Db\Row;
use Dms\Core\Persistence\Db\Schema\Column;
use Dms\Core\Persistence\Db\Schema\Table;

/**
 * Loads a property of new objects with an integer
 * containing the next valid order index integer.
 *
 * The order index numbers a 1-based.
 *
 * This will also automatically resequence the column
 * when a DELETE query is performed to avoid gaps in the
 * sequence.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class OrderIndexPropertyLoaderHook extends PersistHook
{
    /**
     * @var Table
     */
    protected $table;

    /**
     * @var Column
     */
    protected $orderColumn;

    /**
     * @var Column|null
     */
    protected $groupingColumn;

    /**
     * @var string|null
     */
    protected $orderPropertyName;

    /**
     * OrderIndexPropertyLoaderHook constructor.
     *
     * @param string      $idString
     * @param Table       $table
     * @param string      $orderColumn
     * @param string|null $groupingColumn
     * @param string|null $orderPropertyName
     */
    public function __construct(string $idString, Table $table, string $orderColumn, string $groupingColumn = null, string $orderPropertyName = null)
    {
        parent::__construct($idString);

        $this->table             = $table;
        $this->orderColumn       = $table->getColumn($orderColumn);
        $this->groupingColumn    = $groupingColumn ? $table->getColumn($groupingColumn) : null;
        $this->orderPropertyName = $orderPropertyName;
    }

    /**
     * @param PersistenceContext $context
     * @param ITypedObject[]     $objects
     * @param Row[]              $rows
     *
     * @return void
     */
    public function fireBeforePersist(PersistenceContext $context, array $objects, array $rows)
    {
        $orderColumn           = $this->orderColumn->getName();
        $rowsWithoutOrderIndex = [];

        foreach ($rows as $row) {
            if (!$row->hasColumn($orderColumn)) {
                $rowsWithoutOrderIndex[] = $row;
            }
        }

        if ($rowsWithoutOrderIndex) {
            if ($this->groupingColumn) {
                $this->loadGroupedOrderIndexes($context, $rowsWithoutOrderIndex);
            } else {
                $this->loadOrderIndexes($context, $rowsWithoutOrderIndex);
            }
        }
    }

    /**
     * @param PersistenceContext $context
     * @param Row[]              $rows
     *
     * @return void
     */
    protected function loadGroupedOrderIndexes(PersistenceContext $context, array $rows)
    {
        /** @var Row[][] $groupedRows */
        $groupedRows        = [];
        $groupingValues     = [];
        $groupingColumnName = $this->groupingColumn->getName();

        foreach ($rows as $row) {
            $groupingValue = $row->getColumn($groupingColumnName);
            $groupHash     = $groupingValue instanceof \DateTimeInterface
                    ? $groupingValue->format('Y-m-d H:i:s')
                    : $groupingValue;

            $groupingValues[$groupHash] = $groupingValue;
            $groupedRows[$groupHash][]  = $row;
        }

        $groupingColumn = Expr::column($this->table->getName(), $this->groupingColumn);
        $select         = Select::from($this->table)
                ->addColumn('order_index', Expr::max(Expr::column($this->table->getName(), $this->orderColumn)))
                ->addColumn('group', $groupingColumn)
                ->where(Expr::in(
                        $groupingColumn,
                        Expr::tupleParams($this->groupingColumn->getType(), $groupingValues)
                ))
                ->addGroupBy($groupingColumn);

        $orderIndexes        = $context->getConnection()->load($select)->asArray();
        $indexedOrderIndexes = [];

        foreach ($orderIndexes as $orderIndexRow) {
            $groupHash = $orderIndexRow['group'] instanceof \DateTimeInterface
                    ? $orderIndexRow['group']->format('Y-m-d H:i:s')
                    : $orderIndexRow['group'];

            $indexedOrderIndexes[$groupHash] = $orderIndexRow['order_index'];
        }
        
        $orderColumn = $this->orderColumn->getName();

        foreach ($groupedRows as $groupHash => $rows) {
            $orderIndex = isset($indexedOrderIndexes[$groupHash])
                    ? $indexedOrderIndexes[$groupHash] + 1
                    : 1;

            foreach ($rows as $row) {
                $row->setColumn($orderColumn, $orderIndex);
                $orderIndex++;
            }
        }
    }

    /**
     * @param PersistenceContext $context
     * @param Row[]              $rows
     *
     * @return void
     */
    protected function loadOrderIndexes(PersistenceContext $context, array $rows)
    {
        $select = Select::from($this->table)
                ->addColumn('order_index', Expr::max(Expr::column($this->table->getName(), $this->orderColumn)));

        $orderIndexRows = $context->getConnection()->load($select)->asArray();

        $orderIndex = isset($orderIndexRows[0]['order_index'])
                ? $orderIndexRows[0]['order_index'] + 1
                : 1;

        $orderColumn = $this->orderColumn->getName();
        foreach ($rows as $row) {
            $row->setColumn($orderColumn, $orderIndex);
            $orderIndex++;
        }
    }

    /**
     * @param PersistenceContext $context
     * @param ITypedObject[]     $objects
     * @param Row[]              $rows
     *
     * @return void
     */
    public function fireAfterPersist(PersistenceContext $context, array $objects, array $rows)
    {
        if ($this->orderPropertyName) {
            $orderColumn = $this->orderColumn->getName();

            foreach ($objects as $key => $object) {
                $object->hydrate([
                        $this->orderPropertyName => $rows[$key]->getColumn($orderColumn)
                ]);
            }
        }
    }

    /**
     * @param PersistenceContext $context
     * @param Delete             $deleteQuery
     *
     * @return void
     */
    public function fireBeforeDelete(PersistenceContext $context, Delete $deleteQuery)
    {

    }

    /**
     * @param PersistenceContext $context
     * @param Delete             $deleteQuery
     *
     * @return void
     */
    public function fireAfterDelete(PersistenceContext $context, Delete $deleteQuery)
    {
        $context->queue(new ResequenceOrderIndexColumn(
                $this->table,
                $this->orderColumn->getName(),
                $this->groupingColumn ? $this->groupingColumn->getName() : null
        ));
    }

    /**
     * @param string $prefix
     *
     * @return static
     */
    public function withColumnNamesPrefixedBy(string $prefix)
    {
        $clone                 = clone $this;
        $clone->table          = $clone->table->withColumnsPrefixedBy($prefix);
        $clone->orderColumn    = $clone->orderColumn->withPrefix($prefix);
        $clone->groupingColumn = $clone->groupingColumn
                ? $clone->groupingColumn->withPrefix($prefix)
                : null;

        return $clone;
    }
}
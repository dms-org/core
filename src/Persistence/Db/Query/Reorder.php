<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Query;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Exception\InvalidOperationException;
use Dms\Core\Persistence\Db\Connection\IConnection;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Persistence\Db\Schema\Column;
use Dms\Core\Persistence\Db\Schema\Table;
use Dms\Core\Persistence\PersistenceException;

/**
 * The db reorder query class.
 *
 * This will perform queries to update a order index column to
 * a new value, performing the necessary shifting of other rows.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Reorder implements IQuery
{
    /**
     * @var Table
     */
    private $table;

    /**
     * @var Column
     */
    private $orderIndexColumn;

    /**
     * @var Column|null
     */
    private $groupingColumn;

    /**
     * @var int
     */
    private $primaryKey;

    /**
     * @var int
     */
    private $newIndex;

    /**
     * @inheritDoc
     */
    public function __construct(Table $table, $orderIndexColumnName)
    {
        $this->table            = $table;
        $this->orderIndexColumn = $table->getColumn($orderIndexColumnName);
    }


    /**
     * Sets the row primary key.
     *
     * @param int $primaryKey
     *
     * @return static
     */
    public function withPrimaryKey(int $primaryKey)
    {
        $this->primaryKey = $primaryKey;

        return $this;
    }

    /**
     * Sets the new (1-based) order index
     *
     * @param int $newIndex
     *
     * @return static
     * @throws InvalidArgumentException If the index is invalid
     */
    public function toNewIndex(int $newIndex)
    {
        if ($newIndex <= 0) {
            throw InvalidArgumentException::format(
                    'Invalid call to %s: new order index must be >=1, %d given',
                    __METHOD__, $newIndex
            );
        }

        $this->newIndex = $newIndex;

        return $this;
    }

    /**
     * Sets the column which the order indexes are grouped by.
     *
     * @param string $groupingColumnName
     *
     * @return static
     * @throws InvalidArgumentException
     */
    public function groupedBy(string $groupingColumnName)
    {
        $this->groupingColumn = $this->table->getColumn($groupingColumnName);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function executeOn(IConnection $connection)
    {
        if ($this->primaryKey === null || $this->newIndex === null) {
            throw InvalidOperationException::format('Invalid reorder query: primary key and new index must both be supplied');
        }

        $select = Select::from($this->table)
                ->addRawColumn($this->orderIndexColumn->getName());

        if ($this->groupingColumn) {
            $select->addRawColumn($this->groupingColumn->getName());
        }

        $select->where(Expr::equal(
                Expr::column($this->table->getName(), $this->table->getPrimaryKeyColumn()),
                Expr::idParam($this->primaryKey)
        ));

        $currentData = $connection->load($select)->getFirstRowOrNull();

        if (!$currentData) {
            throw PersistenceException::format(
                    'Could not reorder row on table \'%s\', row with primary key \'%s\' no longer exists',
                    $this->table->getName(), $this->primaryKey
            );
        }

        $oldIndexValue = $currentData->getColumn($this->orderIndexColumn->getName());

        if ($oldIndexValue === $this->newIndex) {
            return;
        }

        $connection->withinTransaction(function () use ($connection, $currentData, $oldIndexValue) {
            $primaryKey     = Expr::column($this->table->getName(), $this->table->getPrimaryKeyColumn());
            $orderIndex     = Expr::column($this->table->getName(), $this->orderIndexColumn);
            $columnName     = $this->orderIndexColumn->getName();
            $orderIndexType = $orderIndex->getResultingType();

            $rowId    = Expr::idParam($this->primaryKey);
            $oldIndex = Expr::param($orderIndexType, $oldIndexValue);
            $newIndex = Expr::param($orderIndexType, $this->newIndex);
            $one      = Expr::param($orderIndexType, 1);

            // First update: shift rows in the way of the new index

            if ($this->newIndex > $oldIndexValue) {
                // UPDATE _ SET index = index - 1 WHERE index <= :new_index AND index > :old_index
                $update = Update::from($this->table)
                        ->set($columnName, Expr::subtract($orderIndex, $one))
                        ->where(Expr::lessThanOrEqual($orderIndex, $newIndex))
                        ->where(Expr::greaterThan($orderIndex, $oldIndex));
            } else {
                // UPDATE _ SET index = index + 1 WHERE index >= :new_index AND index < :old_index
                $update = Update::from($this->table)
                        ->set($columnName, Expr::add($orderIndex, $one))
                        ->where(Expr::greaterThanOrEqual($orderIndex, $newIndex))
                        ->where(Expr::lessThan($orderIndex, $oldIndex));
            }

            // Second update: actually move desired row
            $updateRow = Update::from($this->table)
                    ->set($columnName, $newIndex)
                    ->where(Expr::equal($primaryKey, $rowId));


            if ($this->groupingColumn) {
                $currentGroup = $currentData->getColumn($this->groupingColumn->getName());
                $grouping     = Expr::column($this->table->getName(), $this->groupingColumn);
                $inGroup      = Expr::equal(
                        $grouping,
                        Expr::param($grouping->getResultingType(), $currentGroup)
                );

                $update->where($inGroup);
                $updateRow->where($inGroup);
            }

            $connection->update($update);
            $connection->update($updateRow);
        });
    }
}
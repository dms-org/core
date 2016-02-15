<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Locking;

use Dms\Core\Model\ITypedObject;
use Dms\Core\Persistence\Db\PersistenceContext;
use Dms\Core\Persistence\Db\Row;

/**
 * Optimistic locking strategy that maps over a column.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class SinglePropertyLockingStrategy implements IOptimisticLockingStrategy
{
    /**
     * @var string
     */
    protected $propertyName;

    /**
     * @var string
     */
    protected $columnName;

    /**
     * IntegerVersionLockingStrategy constructor.
     *
     * @param string $propertyName
     * @param string $columnName
     */
    public function __construct(string $propertyName, string $columnName)
    {
        $this->propertyName = $propertyName;
        $this->columnName   = $columnName;
    }

    /**
     * @return string[]
     */
    public function getLockingColumnNames() : array
    {
        return [$this->columnName];
    }

    /**
     * @inheritDoc
     */
    public function withColumnNamesPrefixedBy(string $prefix)
    {
        $clone             = clone $this;
        $clone->columnName = $prefix . $clone->columnName;

        return $clone;
    }

    /**
     * @param PersistenceContext $context
     * @param ITypedObject[]     $objects
     * @param Row[]              $rows
     *
     * @return mixed
     */
    public function applyLockingDataAfterCommit(PersistenceContext $context, array $objects, array $rows)
    {
        foreach ($objects as $key => $entity) {
            $entity->hydrate([
                    $this->propertyName => $rows[$key]->getColumn($this->columnName)
            ]);
        }
    }
}
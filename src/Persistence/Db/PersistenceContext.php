<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Model\IEntity;
use Dms\Core\Persistence\Db\Connection\IConnection;
use Dms\Core\Persistence\Db\Connection\Dummy\DummyConnection;
use Dms\Core\Persistence\Db\Mapping\Hook\IPersistHook;
use Dms\Core\Persistence\Db\Mapping\Relation\IRelation;
use Dms\Core\Persistence\Db\Query\BulkUpdate;
use Dms\Core\Persistence\Db\Query\IQuery;
use Dms\Core\Persistence\Db\Query\Upsert;

/**
 * The persistence context class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PersistenceContext extends ConnectionContext
{
    /**
     * @var IRelation[][]
     */
    private $ignoreRelationStack = [];

    /**
     * @var IPersistHook[][]
     */
    private $ignorePersistHookStack = [];

    /**
     * @var IEntity[]
     */
    private $persistedEntities = [];

    /**
     * @var Row[]
     */
    private $persistedEntityRows = [];

    /**
     * @var IQuery[]
     */
    private $operations = [];

    /**
     * @var callable[]
     */
    private $completionCallbacks = [];

    /**
     * @inheritDoc
     */
    public function __construct(IConnection $connection)
    {
        parent::__construct($connection);
    }

    /**
     * Builds a persistence context with a fake connection.
     *
     * @return PersistenceContext
     */
    public static function dummy() : PersistenceContext
    {
        return new PersistenceContext(new DummyConnection());
    }

    /**
     * @param IEntity $entity
     * @param Row     $row
     *
     * @return void
     */
    public function markPersisted(IEntity $entity, Row $row)
    {
        $hash                             = spl_object_hash($entity);
        $this->persistedEntities[$hash]   = $entity;
        $this->persistedEntityRows[$hash] = $row;
    }

    /**
     * @param IEntity $entity
     *
     * @return bool
     */
    public function isPersisted(IEntity $entity) : bool
    {
        return isset($this->persistedEntities[spl_object_hash($entity)]);
    }

    /**
     * @param IEntity $entity
     *
     * @return Row|null
     */
    public function getPersistedRowFor(IEntity $entity)
    {
        $hash = spl_object_hash($entity);

        return isset($this->persistedEntityRows[$hash]) ? $this->persistedEntityRows[$hash] : null;
    }

    /**
     * @param Row $row
     *
     * @return IEntity|null
     */
    public function getPersistedEntityFor(Row $row)
    {
        $entityInstanceHash = array_search($row, $this->persistedEntityRows, true);

        if (!$entityInstanceHash) {
            return null;
        }

        return $this->persistedEntities[$entityInstanceHash];
    }

    /**
     * @param callable    $operation
     * @param IRelation[] $relations
     *
     * @return mixed
     */
    public function ignoreRelationsFor(callable $operation, array $relations)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'relations', $relations, IRelation::class);
        $this->ignoreRelationStack[] = $relations;
        $result                      = $operation();
        array_pop($this->ignoreRelationStack);

        return $result;
    }

    /**
     * @param IRelation $relation
     *
     * @return bool
     */
    public function isRelationIgnored(IRelation $relation) : bool
    {
        if (empty($this->ignoreRelationStack)) {
            return false;
        }

        return in_array($relation, end($this->ignoreRelationStack), true);
    }

    /**
     * @param callable       $operation
     * @param IPersistHook[] $persistHooks
     *
     * @return mixed
     */
    public function ignorePersistHooksFor(callable $operation, array $persistHooks)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'persistHooks', $persistHooks, IPersistHook::class);
        $this->ignorePersistHookStack[] = $persistHooks;
        $result                         = $operation();
        array_pop($this->ignorePersistHookStack);

        return $result;
    }

    /**
     * @param IPersistHook $persistHook
     *
     * @return bool
     */
    public function isPersistHookIgnored(IPersistHook $persistHook) : bool
    {
        if (empty($this->ignorePersistHookStack)) {
            return false;
        }

        return in_array($persistHook, end($this->ignorePersistHookStack), true);
    }

    /**
     * @return IQuery[]
     */
    public function getOperations() : array
    {
        return $this->operations;
    }

    /**
     * @param RowSet   $rows
     * @param string[] $lockingColumnNames
     *
     * @return void
     */
    public function upsert(RowSet $rows, array $lockingColumnNames = [])
    {
        $this->queue(new Upsert($rows, $lockingColumnNames));
    }

    /**
     * @param RowSet $rows
     *
     * @return void
     */
    public function bulkUpdate(RowSet $rows)
    {
        $this->queue(new BulkUpdate($rows));
    }

    /**
     * @param IQuery $operation
     *
     * @return void
     */
    public function queue(IQuery $operation)
    {
        $this->operations[] = $operation;
    }

    /**
     * @param callable $callback
     */
    public function afterCommit(callable $callback)
    {
        $this->completionCallbacks[] = $callback;
    }

    /**
     * Fires the completion callbacks.
     *
     * @return void
     */
    public function fireAfterCommitCallbacks()
    {
        foreach ($this->completionCallbacks as $callback) {
            $callback();
        }
    }
}
<?php declare(strict_types = 1);

namespace Dms\Core\Persistence;

use Dms\Core\Exception;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Exception\TypeMismatchException;
use Dms\Core\Model\EntityNotFoundException;
use Dms\Core\Model\IEntity;
use Dms\Core\Model\ITypedObject;
use Dms\Core\Persistence\Db\Connection\DbOutOfSyncException;
use Dms\Core\Persistence\Db\Connection\IConnection;
use Dms\Core\Persistence\Db\Mapping\EntityOutOfSyncException;
use Dms\Core\Persistence\Db\Mapping\IEntityMapper;
use Dms\Core\Persistence\Db\PersistenceContext;
use Dms\Core\Persistence\Db\Query\Delete;
use Dms\Core\Persistence\Db\Query\Expression\ColumnExpr;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Persistence\Db\Query\Reorder;
use Dms\Core\Persistence\Db\Query\Select;

/**
 * An implementation of the repository using the db orm implementation.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
class DbRepository extends DbRepositoryBase implements IRepository
{
    /**
     * @var IEntityMapper
     */
    protected $mapper;

    /**
     * DbRepository constructor.
     *
     * @param IConnection   $connection
     * @param IEntityMapper $mapper
     */
    public function __construct(IConnection $connection, IEntityMapper $mapper)
    {
        parent::__construct($connection, $mapper);
    }

    /**
     * {@inheritDoc}
     */
    final public function getEntityType() : string
    {
        return $this->mapper->getObjectType();
    }

    /**
     * @return Select
     */
    final protected function select() : Db\Query\Select
    {
        return $this->mapper->getSelect();
    }

    /**
     * @return ColumnExpr
     */
    final protected function primaryKey() : ColumnExpr
    {
        $table = $this->mapper->getPrimaryTable();

        return Expr::column($table->getName(), $table->getPrimaryKeyColumn());
    }

    /**
     * @param array $values
     *
     * @return Db\Query\Expression\Parameter[]
     */
    final protected function parameterIds(array $values) : array
    {
        foreach ($values as $key => $value) {
            $values[$key] = Expr::idParam($value);
        }

        return $values;
    }

    /**
     * Builds a reorder query for the supplied column name.
     *
     * @param string $columnName
     *
     * @return Reorder
     */
    final protected function reorder(string $columnName) : Reorder
    {
        return new Reorder($this->mapper->getPrimaryTable(), $columnName);
    }

    /**
     * {@inheritDoc}
     */
    public function getAll() : array
    {
        return $this->load($this->select());
    }

    /**
     * @inheritDoc
     */
    public function getObjectId(ITypedObject $object)
    {
        $objectType = $this->getObjectType();

        if (!($object instanceof $objectType)) {
            throw TypeMismatchException::argument(__METHOD__, 'object', $objectType, $object);
        }

        /** @var IEntity $object */
        if (!$object->hasId()) {
            throw InvalidArgumentException::format('The supplied entity of type %s does not have an id', get_class($object));
        }

        return $object->getId();
    }

    /**
     * @inheritDoc
     */
    public function count()
    {
        return $this->loadCount($this->select());
    }

    /**
     * {@inheritDoc}
     */
    public function has($id) : bool
    {
        return $this->hasAll([$id]);
    }

    /**
     * {@inheritDoc}
     */
    public function hasAll(array $ids) : bool
    {
        if (empty($ids)) {
            return true;
        }

        $ids = array_unique($ids, SORT_NUMERIC);

        $rows = $this->connection->load(
                $this->select()
                        ->setColumns(['count' => Expr::count()])
                        ->where(Expr::in($this->primaryKey(), Expr::tuple($this->parameterIds($ids))))
        )->asArray();

        return empty($rows) ? false : (int)$rows[0]['count'] === count($ids);
    }

    /**
     * @inheritDoc
     */
    public function contains($object)
    {
        $objectType = $this->getObjectType();

        if (!($object instanceof $objectType)) {
            throw TypeMismatchException::argument(__METHOD__, 'object', $objectType, $object);
        }

        /** @var IEntity $object */

        return $object->hasId()
                ? $this->has($object->getId())
                : false;
    }

    /**
     * @inheritDoc
     */
    public function containsAll(array $objects) : bool
    {
        if (empty($objects)) {
            return true;
        }

        TypeMismatchException::verifyAllInstanceOf(__METHOD__, 'objects', $objects, $this->getObjectType());
        $ids = [];

        /** @var IEntity[] $objects */
        foreach ($objects as $object) {
            $id = $object->getId();

            if ($id !== null) {
                $ids[] = $id;
            }
        }

        return empty($ids) ? false : $this->hasAll($ids);
    }

    /**
     * {@inheritDoc}
     */
    public function get($id) : IEntity
    {
        $entity = $this->tryGet($id);

        if (!$entity) {
            throw new EntityNotFoundException($this->getEntityType(), $id);
        }

        return $entity;
    }

    /**
     * @inheritDoc
     */
    public function getAllById(array $ids) : array
    {
        $ids      = array_unique($ids, SORT_NUMERIC);
        $entities = $this->tryGetAll($ids);

        if (count($entities) !== count($ids)) {
            $idLookup = [];

            foreach ($entities as $entity) {
                $idLookup[$entity->getId()] = true;
            }

            foreach ($ids as $id) {
                if (!isset($idLookup[$id])) {
                    throw new EntityNotFoundException($this->getEntityType(), $id);
                }
            }
        }

        return $entities;
    }

    /**
     * {@inheritDoc}
     */
    public function tryGet($id)
    {
        $entities = $this->tryGetAll([$id]);

        return isset($entities[0]) ? $entities[0] : null;
    }

    /**
     * {@inheritDoc}
     */
    public function tryGetAll(array $ids) : array
    {
        if (empty($ids)) {
            return [];
        }

        InvalidArgumentException::verifyAll(__METHOD__, 'ids', $ids, 'is_int');

        return $this->load(
                $this->select()
                        ->where(Expr::in($this->primaryKey(), Expr::tuple($this->parameterIds($ids))))
        );
    }

    /**
     * {@inheritDoc}
     */
    public function save(ITypedObject $entity)
    {
        $this->saveAll([$entity]);
    }

    /**
     * {@inheritDoc}
     */
    public function saveAll(array $entities)
    {
        $this->transaction(function (PersistenceContext $context) use ($entities) {
            $this->mapper->persistAll($context, $entities);
        });
    }

    final protected function transaction(callable $action)
    {
        $this->connection->beginTransaction();

        try {
            $context = new PersistenceContext($this->connection);
            $action($context);

            foreach ($context->getOperations() as $operation) {
                $operation->executeOn($this->connection);
            }

            $this->connection->commitTransaction();
            $context->fireAfterCommitCallbacks();
        } catch (\Exception $e) {
            $this->connection->rollbackTransaction();

            if ($e instanceof DbOutOfSyncException) {
                /** @var PersistenceContext $context */
                $this->rethrowAsEntityOutOfSyncException($context, $e);
            } else {
                throw $e;
            }
        }
    }

    private function rethrowAsEntityOutOfSyncException(PersistenceContext $context, DbOutOfSyncException $e)
    {
        $entityBeingPersisted = $context->getPersistedEntityFor($e->getRowBeingPersisted());
        $currentEntityInDb    = null;

        if (!($entityBeingPersisted instanceof IEntity)) {
            throw $e;
        }

        $mapper = $this->mapper->findMapperFor(get_class($entityBeingPersisted));

        if (!$mapper) {
            throw $e;
        }

        /** @var IEntity $currentEntityInDb */
        $currentRowInDb    = $e->getCurrentRowInDb();
        $currentEntityInDb = $currentRowInDb
                ? $mapper->load($this->loadingContext, $currentRowInDb)
                : null;

        throw new EntityOutOfSyncException(
                $entityBeingPersisted,
                $currentEntityInDb,
                $e
        );
    }

    /**
     * {@inheritDoc}
     */
    public function remove($entity)
    {
        $this->removeAll([$entity]);
    }

    /**
     * {@inheritDoc}
     */
    public function removeAll(array $entities)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'entities', $entities, $this->getEntityType());

        $ids = [];

        /** @var IEntity[] $entities */
        foreach ($entities as $entity) {
            if ($entity->hasId()) {
                $ids[] = $entity->getId();
            }
        }

        $this->transaction(function (PersistenceContext $context) use ($ids) {
            $this->mapper->deleteAll($context, $ids);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function removeById($id)
    {
        $this->removeAllById([$id]);
    }

    /**
     * {@inheritDoc}
     */
    public function removeAllById(array $ids)
    {
        $this->transaction(function (PersistenceContext $context) use ($ids) {
            $this->mapper->deleteAll($context, $ids);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function clear()
    {
        $this->transaction(function (PersistenceContext $context) {
            $this->mapper->deleteFromQuery($context, Delete::from($this->mapper->getPrimaryTable()));
        });
    }
}

<?php

namespace Dms\Core\Persistence;

use Dms\Core\Exception;
use Dms\Core\Model\ICriteria;
use Dms\Core\Model\IEntity;
use Dms\Core\Model\ILoadCriteria;
use Dms\Core\Model\IObjectSetWithLoadCriteriaSupport;
use Dms\Core\Model\ISpecification;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Persistence\Db\Connection\IConnection;
use Dms\Core\Persistence\Db\Criteria\CriteriaMapper;
use Dms\Core\Persistence\Db\Criteria\LoadCriteriaMapper;
use Dms\Core\Persistence\Db\LoadingContext;
use Dms\Core\Persistence\Db\Mapping\IEntityMapper;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Persistence\Db\Query\IQuery;
use Dms\Core\Persistence\Db\Query\Select;

/**
 * An base class of the db repository.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
abstract class DbRepositoryBase implements IObjectSetWithLoadCriteriaSupport
{
    /**
     * @var LoadingContext
     */
    protected $loadingContext;

    /**
     * @var IConnection
     */
    protected $connection;

    /**
     * @var IEntityMapper
     */
    protected $mapper;

    /**
     * @var CriteriaMapper
     */
    protected $criteriaMapper;

    /**
     * @var LoadCriteriaMapper
     */
    protected $loadCriteriaMapper;

    /**
     * DbRepositoryBase constructor.
     *
     * @param IConnection   $connection
     * @param IEntityMapper $mapper
     */
    public function __construct(IConnection $connection, IEntityMapper $mapper)
    {
        $this->connection         = $connection;
        $this->loadingContext     = new LoadingContext($connection);
        $this->criteriaMapper     = new CriteriaMapper($mapper, $connection);
        $this->loadCriteriaMapper = new LoadCriteriaMapper($this->criteriaMapper);
        $this->mapper             = $mapper;
    }

    /**
     * @return IConnection
     */
    final public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @return IEntityMapper
     */
    final public function getMapper()
    {
        return $this->mapper;
    }

    /**
     * {@inheritDoc}
     */
    final public function getElementType()
    {
        return Type::object($this->mapper->getObjectType());
    }

    /**
     * {@inheritDoc}
     */
    final public function getObjectType()
    {
        return $this->mapper->getObjectType();
    }

    /**
     * @param Select $query
     *
     * @return IEntity[]
     */
    protected function load(Select $query)
    {
        $rows = $this->connection->load($query);

        return $this->mapper->loadAll($this->loadingContext, $rows->getRows());
    }

    /**
     * @param Select $select
     *
     * @return int
     */
    protected function loadCount(Select $select)
    {
        $limit  = $select->getLimit();
        $offset = $select->getOffset();

        $select->offset(0)->limit(null);

        $rows = $this->connection->load(
                $select->setColumns(['count' => Expr::count()])
        )->asArray();

        if (empty($rows)) {
            return 0;
        }

        $count = (int)$rows[0]['count'] - $offset;

        return $limit !== null ? min($limit, $count) : $count;
    }

    /**
     * Executes the supplied query on the current db connection.
     *
     * @param IQuery $query
     *
     * @return mixed
     */
    protected function execute(IQuery $query)
    {
        return $query->executeOn($this->connection);
    }

    /**
     * {@inheritDoc}
     */
    public function criteria()
    {
        return $this->criteriaMapper->newCriteria();
    }

    /**
     * {@inheritDoc}
     */
    public function loadCriteria()
    {
        return $this->loadCriteriaMapper->newCriteria();
    }

    /**
     * {@inheritDoc}
     */
    public function countMatching(ICriteria $criteria)
    {
        return $this->loadCount($this->criteriaMapper->mapCriteriaToSelect($criteria));
    }

    /**
     * {@inheritDoc}
     */
    public function matching(ICriteria $criteria)
    {
        return $this->load($this->criteriaMapper->mapCriteriaToSelect($criteria));
    }

    /**
     * {@inheritDoc}
     */
    public function satisfying(ISpecification $specification)
    {
        return $this->matching($specification->asCriteria());
    }

    /**
     * @inheritDoc
     */
    public function loadMatching(ILoadCriteria $criteria)
    {
        $criteria->verifyOfClass($this->getObjectType());

        $mappedQuery = $this->loadCriteriaMapper->mapLoadCriteriaToQuery($criteria);

        return $mappedQuery->load($this->loadingContext);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->getAll());
    }
}

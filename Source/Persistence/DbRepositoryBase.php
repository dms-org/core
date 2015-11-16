<?php

namespace Iddigital\Cms\Core\Persistence;

use Iddigital\Cms\Core\Exception;
use Iddigital\Cms\Core\Model\Criteria\PartialLoadCriteria;
use Iddigital\Cms\Core\Model\ICriteria;
use Iddigital\Cms\Core\Model\IEntity;
use Iddigital\Cms\Core\Model\IObjectSetWithPartialLoadSupport;
use Iddigital\Cms\Core\Model\IPartialLoadCriteria;
use Iddigital\Cms\Core\Model\ISpecification;
use Iddigital\Cms\Core\Persistence\Db\Connection\IConnection;
use Iddigital\Cms\Core\Persistence\Db\Criteria\CriteriaMapper;
use Iddigital\Cms\Core\Persistence\Db\LoadingContext;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IObjectMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\ReadModel\ArrayReadModelMapper;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\Expr;
use Iddigital\Cms\Core\Persistence\Db\Query\IQuery;
use Iddigital\Cms\Core\Persistence\Db\Query\Select;

/**
 * An base class of the db repository.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
abstract class DbRepositoryBase implements IObjectSetWithPartialLoadSupport
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
     * @var IObjectMapper
     */
    protected $mapper;

    /**
     * @var CriteriaMapper
     */
    protected $criteriaMapper;

    /**
     * DbRepository constructor.
     *
     * @param IConnection   $connection
     * @param IObjectMapper $mapper
     */
    public function __construct(IConnection $connection, IObjectMapper $mapper)
    {
        $this->connection     = $connection;
        $this->loadingContext = new LoadingContext($connection);
        $this->criteriaMapper = new CriteriaMapper($mapper);
        $this->mapper         = $mapper;
    }

    /**
     * {@inheritDoc}
     */
    final public function getElementType()
    {
        return $this->mapper->getObjectType();
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
    public function partialCriteria()
    {
        return new PartialLoadCriteria($this->mapper->getDefinition()->getClass());
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
    public function loadPartial(IPartialLoadCriteria $criteria)
    {
        $criteria->verifyOfClass($this->getObjectType());

        $aliasPropertyNameMap = $criteria->getAliasNestedMemberStringMap();
        $readModelMapper      = new ArrayReadModelMapper(
                $this->mapper,
                $aliasPropertyNameMap
        );

        $criteriaMapper = new CriteriaMapper($readModelMapper);
        $select         = $criteriaMapper->mapCriteriaToSelect($criteria);
        $rows           = $this->connection->load($select);

        return $readModelMapper->loadAllAsArray($this->loadingContext, $rows->getRows());
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->getAll());
    }
}

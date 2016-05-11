<?php declare(strict_types = 1);

namespace Dms\Core\Persistence;

use Dms\Core\Exception;
use Dms\Core\Model\Criteria\Criteria;
use Dms\Core\Model\Criteria\LoadCriteria;
use Dms\Core\Model\ICriteria;
use Dms\Core\Model\IEntity;
use Dms\Core\Model\ILoadCriteria;
use Dms\Core\Model\IObjectSetWithLoadCriteriaSupport;
use Dms\Core\Model\ISpecification;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Model\Type\IType;
use Dms\Core\Persistence\Db\Connection\IConnection;
use Dms\Core\Persistence\Db\Criteria\CriteriaMapper;
use Dms\Core\Persistence\Db\Criteria\LoadCriteriaMapper;
use Dms\Core\Persistence\Db\LoadingContext;
use Dms\Core\Persistence\Db\Mapping\IEntityMapper;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Persistence\Db\Query\IQuery;
use Dms\Core\Persistence\Db\Query\Select;
use Dms\Core\Persistence\Db\Row;
use Dms\Core\Util\Debug;

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
    final public function getConnection() : IConnection
    {
        return $this->connection;
    }

    /**
     * @return IEntityMapper
     */
    final public function getMapper() : IEntityMapper
    {
        return $this->mapper;
    }

    /**
     * {@inheritDoc}
     */
    final public function getElementType() : IType
    {
        return Type::object($this->mapper->getObjectType());
    }

    /**
     * {@inheritDoc}
     */
    final public function getObjectType() : string
    {
        return $this->mapper->getObjectType();
    }

    /**
     * @param Select $query
     *
     * @return IEntity[]
     */
    protected function load(Select $query) : array
    {
        $rows = $this->connection->load($query);

        return $this->mapper->loadAll($this->loadingContext, $rows->getRows());
    }

    /**
     * Loads an array of entities from the supplied sql query.
     *
     * The the table name can be interpolated by the "(table)" placeholder
     * The required columns can be interpolated by using the "(columns)" placeholder
     *
     * Example:
     * <code>
     * $entities = $this->loadRows(
     *      'SELECT (columns) FROM (table) table_alias WHERE some_column > :param',
     *      ['param' => 10]
     * );
     * </code>
     *
     * @param string $sql
     * @param array  $parameters
     *
     * @return IEntity[]
     */
    protected function loadQuery(string $sql, array $parameters = []) : array
    {
        $sql   = $this->replaceQueryPlaceholders($sql);
        $query = $this->connection->prepare($sql, $parameters);
        $rows  = $query->execute()->getResults();

        if (empty($rows)) {
            return [];
        }

        $this->validateHasRequiredColumns($sql, reset($rows));

        $rowSet = $this->connection->getPlatform()->mapResultSetToPhpForm($this->mapper->getSelect()->getResultSetTableStructure(), $rows);

        return $this->mapper->loadAll($this->loadingContext, $rowSet->getRows());
    }

    protected function replaceQueryPlaceholders(string $sql)
    {
        $columnsToLoad = $this->mapper->getMapping()->getAllColumnsToLoad();
        /** @var callable $quoteIdentifier */
        $quoteIdentifier = [$this->connection->getPlatform(), 'quoteIdentifier'];
        $placeholders    = [
            '(columns)' => implode(', ', array_map($quoteIdentifier, $columnsToLoad)),
            '(table)'   => $quoteIdentifier($this->mapper->getPrimaryTableName()),
        ];

        return strtr($sql, $placeholders);
    }

    protected function validateHasRequiredColumns(string $sql, array $row)
    {
        $requiredColumns = array_fill_keys($this->mapper->getMapping()->getAllColumnsToLoad(), true);

        if ($missingColumns = array_diff_key($requiredColumns, $row)) {
            throw PersistenceException::format(
                'The supplied query \'%s\' does not load the required columns to load the %s entity: expecting all of (%s), (%s) given which is missing (%s)',
                $sql, $this->getObjectType(), Debug::formatValues(array_keys($requiredColumns)),
                Debug::formatValues(array_keys($row)), Debug::formatValues(array_keys($missingColumns))
            );
        }
    }

    protected function quoteIdentifier()
    {

    }

    /**
     * @param Select $select
     *
     * @return int
     */
    protected function loadCount(Select $select) : int
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
    public function criteria() : Criteria
    {
        return $this->criteriaMapper->newCriteria();
    }

    /**
     * {@inheritDoc}
     */
    public function loadCriteria() : LoadCriteria
    {
        return $this->loadCriteriaMapper->newCriteria();
    }

    /**
     * {@inheritDoc}
     */
    public function countMatching(ICriteria $criteria) : int
    {
        return $this->loadCount($this->criteriaMapper->mapCriteriaToSelect($criteria));
    }

    /**
     * {@inheritDoc}
     */
    public function matching(ICriteria $criteria) : array
    {
        return $this->load($this->criteriaMapper->mapCriteriaToSelect($criteria));
    }

    /**
     * {@inheritDoc}
     */
    public function satisfying(ISpecification $specification) : array
    {
        return $this->matching($specification->asCriteria());
    }

    /**
     * @inheritDoc
     */
    public function loadMatching(ILoadCriteria $criteria) : array
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

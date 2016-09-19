<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Hierarchy;

use Dms\Core\Model\ITypedObject;
use Dms\Core\Persistence\Db\LoadingContext;
use Dms\Core\Persistence\Db\Mapping\Definition\FinalizedMapperDefinition;
use Dms\Core\Persistence\Db\Mapping\IObjectMapper;
use Dms\Core\Persistence\Db\PersistenceContext;
use Dms\Core\Persistence\Db\Query\Delete;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Persistence\Db\Query\Query;
use Dms\Core\Persistence\Db\Query\Select;
use Dms\Core\Persistence\Db\Row;
use Dms\Core\Persistence\Db\Schema\Table;

/**
 * The class mapping interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IObjectMapping
{
    /**
     * @return string
     */
    public function getObjectType() : string;

    /**
     * @return FinalizedMapperDefinition
     */
    public function getDefinition() : \Dms\Core\Persistence\Db\Mapping\Definition\FinalizedMapperDefinition;

    /**
     * @param IObjectMapper $parentMapper
     */
    public function initializeRelations(IObjectMapper $parentMapper);

    /**
     * Adds the required clauses to load the data for the
     * instances of this class to the select.
     *
     * @param Select $select
     * @param string $tableAlias
     * @param array  $subclassTableAliases
     *
     * @return void
     */
    public function addLoadToSelect(Select $select, string $tableAlias, array &$subclassTableAliases = []);

    /**
     * Adds the required clauses to load ONLY the instances of this class
     * to the query. If the query is a select it will add the necessary columns.
     *
     * @param Query  $query
     * @param string $objectType
     * @param array  $subclassTableAliases
     *
     * @return void
     */
    public function addSpecificLoadToQuery(Query $query, string $objectType, array &$subclassTableAliases = []);

    /**
     * Returns an expression to match only instances of this class.
     *
     * @param Query  $query
     * @param string $objectType
     *
     * @return Expr
     */
    public function getClassConditionExpr(Query $query, string $objectType) : \Dms\Core\Persistence\Db\Query\Expression\Expr;

    /**
     * Constructs new objects from the supplied row.
     *
     * Note: indexes are maintained.
     *
     * @param Row $row
     *
     * @return ITypedObject
     */
    public function constructNewObjectFromRow(Row $row) : \Dms\Core\Model\ITypedObject;

    /**
     * Returns whether the supplied rows match the object type.
     *
     * @param Row $row
     *
     * @return bool
     */
    public function rowMatchesType(Row $row) : bool;

    /**
     * Returns the columns required to load the object.
     *
     * @return string[]
     */
    public function getAllColumnsToLoad() : array;

    /**
     * Gets the dependency mode or NULL if it is the root object.
     *
     * @return string|null
     */
    public function getDependencyMode();

    /**
     * @return Table[]
     */
    public function getMappingTables() : array;

    /**
     * @param string $prefix
     *
     * @return static
     */
    public function withEmbeddedColumnsPrefixedBy(string $prefix);

    /**
     * Hydrates the objects by mapping the row data to the column data.
     * The rows are mapped to the corresponding object instance via the array keys.
     *
     * @param LoadingContext $context
     * @param ITypedObject[] $objects
     * @param Row[]          $rows
     *
     * @return void
     */
    public function loadAll(LoadingContext $context, array $objects, array $rows);

    /**
     * Loads the an array containing the object properties from the supplied rows.
     *
     * NOTE: indexes are maintained.
     *
     * @param LoadingContext $context
     * @param Row[]          $rows
     * @param ITypedObject[] $objects
     *
     * @return array[]
     */
    public function loadAllProperties(LoadingContext $context, array $rows, array $objects) : array;

    /**
     * @param PersistenceContext $context
     * @param ITypedObject[]     $objects
     * @param Row[]              $rows
     */
    public function persistAll(PersistenceContext $context, array $objects, array $rows);

    /**
     * Adds the necessary queries to the context to delete the
     * supplied entities.
     *
     * @param PersistenceContext $context
     * @param Delete             $deleteQuery
     */
    public function delete(PersistenceContext $context, Delete $deleteQuery);
}
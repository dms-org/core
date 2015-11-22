<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Relation;

use Iddigital\Cms\Core\Persistence\Db\Mapping\ParentMapBase;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\Expr;
use Iddigital\Cms\Core\Persistence\Db\Query\Select;
use Iddigital\Cms\Core\Persistence\Db\Row;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;

/**
 * The relation interface for relations that map to objects stored in another table.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface ISeparateTableRelation extends IRelation
{
    /**
     * Builds a select query to select the related rows for the parent ids.
     *
     * NOTE: The table from which the subselect is from must be the related primary table.
     *
     * @param ParentMapBase $map
     * @param string &$parentIdColumnName This is an out parameter
     *
     * @return Select
     */
    public function getRelationSelectFromParentRows(ParentMapBase $map, &$parentIdColumnName = null);

    /**
     * Builds a select query to select the related rows as a sub select.
     *
     * This should use {@see Select::buildSubSelect} for the new instance.
     *
     * NOTE: The table from which the subselect is from must be the related primary table.
     *
     * @param Select $outerSelect
     * @param string $parentTableAlias
     *
     * @return Select
     */
    public function getRelationSubSelect(Select $outerSelect, $parentTableAlias);

    /**
     * Gets the condition to join the related parent table to the child table,
     * usually via a foreign key.
     *
     * @param string $parentTableAlias
     * @param string $relatedTableAlias This refers to the table returned from {@see getRelationChildTable}
     *
     * @return Expr
     */
    public function getRelationJoinCondition($parentTableAlias, $relatedTableAlias);

    /**
     * Adds a join to the supplied select to the related table.
     *
     * @see Join for join type options
     *
     * @param string $parentTableAlias
     * @param string $joinType
     * @param Select $select
     *
     * @return string The alias of the joined table
     */
    public function joinSelectToRelatedTable($parentTableAlias, $joinType, Select $select);
}
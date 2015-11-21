<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Relation;

use Iddigital\Cms\Core\Persistence\Db\Query\Expression\Expr;
use Iddigital\Cms\Core\Persistence\Db\Query\Select;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;

/**
 * The relation interface for relations that map to objects stored in another table.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface ISeparateTableRelation extends IRelation
{
    /**
     * Gets the table which to join-to / select from to load the relation
     * from a subselect.
     *
     * @return Table
     */
    public function getRelationSelectTable();

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
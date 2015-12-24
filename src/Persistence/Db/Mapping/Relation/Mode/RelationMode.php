<?php

namespace Dms\Core\Persistence\Db\Mapping\Relation\Mode;

use Dms\Core\Persistence\Db\Query\Clause\Join;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Persistence\Db\Query\Query;
use Dms\Core\Persistence\Db\Schema\Column;

/**
 * The relation mode base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class RelationMode implements IRelationMode
{

    /**
     * @param Query  $newQuery
     * @param Query  $parentQuery
     * @param Column $foreignKeyToParentColumn
     *
     * @param Column $parentKeyColumn
     *
     * @return Query
     */
    final protected function foreignKeyJoin(Query $newQuery, Query $parentQuery, Column $foreignKeyToParentColumn, Column $parentKeyColumn)
    {
        $parentTable = $parentQuery->getTable();
        $tableAlias  = $newQuery->generateUniqueAliasFor($parentTable->getName());

        return $newQuery->prependJoin(Join::inner($parentTable, $tableAlias, [
                Expr::equal(
                        Expr::column($newQuery->getTableAlias(), $foreignKeyToParentColumn),
                        Expr::column($tableAlias, $parentKeyColumn)
                )
        ]));
    }
}
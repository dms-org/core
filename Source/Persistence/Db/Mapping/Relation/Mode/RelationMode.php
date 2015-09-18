<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Mode;

use Iddigital\Cms\Core\Persistence\Db\Query\Clause\Join;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\Expr;
use Iddigital\Cms\Core\Persistence\Db\Query\Query;
use Iddigital\Cms\Core\Persistence\Db\Schema\Column;

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
        $tableAlias  = $newQuery->getAliasFor($parentTable->getName());

        return $newQuery->prependJoin(Join::inner($parentTable, $tableAlias, [
                Expr::equal(
                        Expr::column($newQuery->getTableAlias(), $foreignKeyToParentColumn),
                        Expr::column($tableAlias, $parentKeyColumn)
                )
        ]));
    }
}
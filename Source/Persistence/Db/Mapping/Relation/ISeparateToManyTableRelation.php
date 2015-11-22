<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Relation;

use Iddigital\Cms\Core\Persistence\Db\LoadingContext;
use Iddigital\Cms\Core\Persistence\Db\Mapping\ParentChildrenMap;
use Iddigital\Cms\Core\Persistence\Db\Query\Select;

/**
 * The relation interface for relations that map to objects stored in another table.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface ISeparateToManyTableRelation extends ISeparateTableRelation, IToManyRelation
{
    /**
     * @param LoadingContext $context
     * @param ParentChildrenMap $map
     * @param Select $select
     * @param string $relatedTableAlias
     * @param string $parentIdColumnName
     *
     * @return void
     */
    public function loadFromSelect(
            LoadingContext $context,
            ParentChildrenMap $map,
            Select $select,
            $relatedTableAlias,
            $parentIdColumnName
    );
}
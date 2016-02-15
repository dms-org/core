<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Relation;

use Dms\Core\Persistence\Db\LoadingContext;
use Dms\Core\Persistence\Db\Mapping\ParentChildrenMap;
use Dms\Core\Persistence\Db\Query\Select;

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
            string $relatedTableAlias,
            string $parentIdColumnName
    );
}
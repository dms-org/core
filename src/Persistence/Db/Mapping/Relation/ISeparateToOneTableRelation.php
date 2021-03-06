<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Relation;

use Dms\Core\Persistence\Db\LoadingContext;
use Dms\Core\Persistence\Db\Mapping\ParentChildMap;
use Dms\Core\Persistence\Db\Query\Select;

/**
 * The relation interface for relations that map to objects stored in another table.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface ISeparateToOneTableRelation extends ISeparateTableRelation, IToOneRelation
{
    /**
     * @param LoadingContext $context
     * @param ParentChildMap $map
     * @param Select         $select
     * @param string         $relatedTableAlias
     * @param string         $parentIdColumnName
     *
     * @return void
     */
    public function loadFromSelect(LoadingContext $context, ParentChildMap $map, Select $select, string $relatedTableAlias, string $parentIdColumnName);
}
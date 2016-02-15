<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Relation\Reference;

use Dms\Core\Persistence\Db\Mapping\IEntityMapper;
use Dms\Core\Persistence\Db\Mapping\Relation\IRelation;
use Dms\Core\Persistence\Db\Query\Select;

/**
 * The relation reference type interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IRelationReference
{
    /**
     * @return IEntityMapper
     */
    public function getMapper() : \Dms\Core\Persistence\Db\Mapping\IEntityMapper;

    /**
     * @param Select $select
     * @param string $relatedTableAlias
     *
     * @return void
     */
    public function addLoadToSelect(Select $select, string $relatedTableAlias);

    /**
     * @param mixed $childValue
     *
     * @return int|null
     */
    public function getIdFromValue($childValue);

    /**
     * @return IRelation|null
     */
    public function getBidirectionalRelation();
}
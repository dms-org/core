<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Reference;

use Iddigital\Cms\Core\Persistence\Db\Mapping\IEntityMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\IRelation;
use Pinq\Queries\Segments\Select;

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
    public function getMapper();

    /**
     * @return Select
     */
    public function getSelect();

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
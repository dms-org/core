<?php

namespace Iddigital\Cms\Core\Persistence\Db\Criteria\MemberMapping;

use Iddigital\Cms\Core\Persistence\Db\Mapping\ReadModel\Relation\MemberRelation;

/**
 * The final relation member mapping interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IFinalRelationMemberMapping
{
    /**
     * @return MemberRelation
     */
    public function asMemberRelation();
}
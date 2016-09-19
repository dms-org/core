<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Criteria\MemberMapping;

use Dms\Core\Persistence\Db\Mapping\Hierarchy\IObjectMapping;
use Dms\Core\Persistence\Db\Mapping\IEntityMapper;
use Dms\Core\Persistence\Db\Mapping\Relation\IRelation;
use Dms\Core\Persistence\Db\Mapping\Relation\IToOneRelation;

/**
 * The to-one relation mapping base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class ToOneRelationMapping extends RelationMapping
{
    /**
     * ToOneRelationMapping constructor.
     *
     * @param IEntityMapper    $rootEntityMapper
     * @param IObjectMapping[] $subclassObjectMappings
     * @param IRelation[]      $relationsToSubSelect
     * @param IToOneRelation   $relation
     */
    public function __construct(IEntityMapper $rootEntityMapper, array $subclassObjectMappings, array $relationsToSubSelect, IToOneRelation $relation)
    {
        parent::__construct($rootEntityMapper, $subclassObjectMappings, $relationsToSubSelect, $relation);
    }
}
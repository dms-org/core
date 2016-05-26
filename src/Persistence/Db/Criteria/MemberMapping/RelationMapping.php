<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Criteria\MemberMapping;

use Dms\Core\Exception\InvalidOperationException;
use Dms\Core\Persistence\Db\Mapping\IEntityMapper;
use Dms\Core\Persistence\Db\Mapping\Relation\IRelation;
use Dms\Core\Persistence\Db\Mapping\Relation\IToManyRelation;
use Dms\Core\Persistence\Db\Mapping\Relation\IToOneRelation;

/**
 * The relation mapping base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class RelationMapping extends MemberMapping
{
    /**
     * @var IRelation
     */
    private $lastRelation;

    /**
     * RelationMapping constructor.
     *
     * @param IEntityMapper $rootEntityMapper
     * @param IRelation[]   $relationsToSubSelect
     * @param IRelation     $relation
     */
    public function __construct(IEntityMapper $rootEntityMapper, array $relationsToSubSelect, IRelation $relation)
    {
        parent::__construct($rootEntityMapper, array_merge($relationsToSubSelect, [$relation]));

        $this->lastRelation = $relation;
    }

    /**
     * @return IToManyRelation|IToOneRelation
     * @throws InvalidOperationException
     */
    public function getRelation()
    {
        return end($this->relationsToSubSelect) ?: $this->lastRelation;
    }

    /**
     * @return IRelation
     * @throws InvalidOperationException
     */
    public function getFirstRelation() : IRelation
    {
        return reset($this->relationsToSubSelect) ?: $this->lastRelation;
    }

    /**
     * @return string
     */
    protected function getRelatedObjectType() : string
    {
        return $this->getRelation()->getMapper()->getObjectType();
    }
}
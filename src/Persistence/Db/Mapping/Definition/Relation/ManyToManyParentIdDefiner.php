<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Definition\Relation;

/**
 * The many-to-many relation definer class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ManyToManyParentIdDefiner extends ManyToManyRelationDefinerBase
{
    /**
     * Defines the column in the join table to map the parent id to.
     *
     * @param string $column
     *
     * @return ManyToManyRelatedIdDefiner
     */
    public function withParentIdAs(string $column) : ManyToManyRelatedIdDefiner
    {
        return new ManyToManyRelatedIdDefiner(
                $this->callback,
                $this->mapperLoader,
                $this->joinTableName,
                $column,
                $this->bidirectionalRelationProperty,
                $this->loadIds
        );
    }
}
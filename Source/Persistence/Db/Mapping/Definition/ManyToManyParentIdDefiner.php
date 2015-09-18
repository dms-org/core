<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Definition;

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
    public function withParentIdAs($column)
    {
        return new ManyToManyRelatedIdDefiner(
                $this->callback,
                $this->mapper,
                $this->joinTable,
                $column,
                $this->bidirectionalRelationProperty,
                $this->loadIds
        );
    }
}
<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\Relation;

use Iddigital\Cms\Core\Persistence\Db\Mapping\IEntityMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\ManyToManyRelation;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Reference\ToManyRelationIdentityReference;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Reference\ToManyRelationObjectReference;

/**
 * The many-to-many relation definer class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ManyToManyRelatedIdDefiner extends ManyToManyRelationDefinerBase
{
    /**
     * @var string
     */
    private $parentIdColumn;

    public function __construct(callable $callback, IEntityMapper $mapper, $joinTable, $parentIdColumn, $bidirectionalRelationProperty, $loadIds)
    {
        parent::__construct($callback, $mapper, $joinTable, $bidirectionalRelationProperty, $loadIds);
        $this->parentIdColumn = $parentIdColumn;
    }

    /**
     * Defines the column in the join table to map the related id to.
     *
     * @param string $column
     *
     * @return void
     */
    public function withRelatedIdAs($column)
    {
        call_user_func($this->callback, function () use ($column) {
            return new ManyToManyRelation(
                    $this->loadIds
                            ? new ToManyRelationIdentityReference($this->mapper)
                            : new ToManyRelationObjectReference($this->mapper, $this->bidirectionalRelationProperty),
                    $this->joinTable,
                    $this->parentIdColumn,
                    $column
            );
        });
    }
}
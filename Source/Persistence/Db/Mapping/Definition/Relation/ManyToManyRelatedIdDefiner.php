<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\Relation;

use Iddigital\Cms\Core\Persistence\Db\Mapping\IEntityMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\ManyToManyRelation;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Reference\ToManyRelationIdentityReference;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Reference\ToManyRelationObjectReference;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;

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

    public function __construct(callable $callback, callable $mapperLoader, $joinTable, $parentIdColumn, $bidirectionalRelationProperty, $loadIds)
    {
        parent::__construct($callback, $mapperLoader, $joinTable, $bidirectionalRelationProperty, $loadIds);
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
        call_user_func($this->callback, function (Table $parentTable) use ($column) {
            $mapper = call_user_func($this->mapperLoader);

            return new ManyToManyRelation(
                    $this->loadIds
                            ? new ToManyRelationIdentityReference($mapper)
                            : new ToManyRelationObjectReference($mapper, $this->bidirectionalRelationProperty),
                    $this->joinTableName,
                    $parentTable->getName(),
                    $parentTable->getPrimaryKeyColumnName(),
                    $this->parentIdColumn,
                    $column
            );
        });
    }
}
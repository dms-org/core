<?php

namespace Dms\Core\Persistence\Db\Mapping\Definition\Relation;

use Dms\Core\Persistence\Db\Mapping\Relation\ManyToManyRelation;
use Dms\Core\Persistence\Db\Mapping\Relation\Reference\ToManyRelationIdentityReference;
use Dms\Core\Persistence\Db\Mapping\Relation\Reference\ToManyRelationObjectReference;
use Dms\Core\Persistence\Db\Schema\Table;

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

    public function __construct(
            callable $callback,
            callable $mapperLoader,
            $joinTable,
            $parentIdColumn,
            $bidirectionalRelationProperty,
            $loadIds
    ) {
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
        call_user_func($this->callback, function ($idString, Table $parentTable) use ($column) {
            $mapper = call_user_func($this->mapperLoader);

            return new ManyToManyRelation(
                    $idString,
                    $this->loadIds
                            ? new ToManyRelationIdentityReference($mapper, $this->bidirectionalRelationProperty)
                            : new ToManyRelationObjectReference($mapper, $this->bidirectionalRelationProperty),
                    $this->joinTableName,
                    $parentTable->getName(),
                    $parentTable->getPrimaryKeyColumn(),
                    $this->parentIdColumn,
                    $column
            );
        });
    }
}
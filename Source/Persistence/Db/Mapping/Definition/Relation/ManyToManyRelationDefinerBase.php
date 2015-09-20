<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\Relation;

/**
 * The many-to-many relation definer class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ManyToManyRelationDefinerBase extends RelationTypeDefinerBase
{
    /**
     * @var string
     */
    protected $joinTableName;

    /**
     * @var string
     */
    protected $bidirectionalRelationProperty;

    public function __construct(callable $callback, callable $mapperLoader, $joinTable, $bidirectionalRelationProperty, $loadIds)
    {
        parent::__construct($callback, $mapperLoader, $loadIds);
        $this->joinTableName                 = $joinTable;
        $this->bidirectionalRelationProperty = $bidirectionalRelationProperty;
    }
}
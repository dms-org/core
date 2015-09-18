<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Definition;

use Iddigital\Cms\Core\Persistence\Db\Mapping\IEntityMapper;

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
    protected $joinTable;

    /**
     * @var string
     */
    protected $bidirectionalRelationProperty;

    public function __construct(callable $callback, IEntityMapper $mapper, $joinTable, $bidirectionalRelationProperty, $loadIds)
    {
        parent::__construct($callback, $mapper, $loadIds);
        $this->joinTable                     = $joinTable;
        $this->bidirectionalRelationProperty = $bidirectionalRelationProperty;
    }
}
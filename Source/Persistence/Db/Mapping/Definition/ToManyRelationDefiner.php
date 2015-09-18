<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Definition;

use Iddigital\Cms\Core\Persistence\Db\Mapping\IEntityMapper;

/**
 * The to-many relation definer class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ToManyRelationDefiner extends OneToManyRelationDefiner
{
    public function __construct(callable $callback, IEntityMapper $mapper, $loadIds)
    {
        parent::__construct($callback, $mapper, $loadIds, $identifying = false);
    }

    /**
     * Defines the relationship as a one-to-many identifying relationship.
     *
     * This means children entities without a parent will be deleted.
     *
     * @return OneToManyRelationDefiner
     */
    public function identifying()
    {
        return new OneToManyRelationDefiner($this->callback, $this->mapper, $this->loadIds, $identifying = true);
    }

    /**
     * Defines the relationship as many-to-many mapping through
     * the supplied join table.
     *
     * @param string $tableName
     *
     * @return ManyToManyParentIdDefiner
     */
    public function throughJoinTable($tableName)
    {
        return new ManyToManyParentIdDefiner($this->callback, $this->mapper, $tableName, $this->bidirectionalRelationProperty,  $this->loadIds);
    }
}
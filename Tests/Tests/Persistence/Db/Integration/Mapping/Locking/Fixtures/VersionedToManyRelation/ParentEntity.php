<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Locking\Fixtures\VersionedToManyRelation;

use Iddigital\Cms\Core\Model\IEntityCollection;
use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\Entity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ParentEntity extends Entity
{
    /**
     * @var IEntityCollection|ChildEntity[]
     */
    public $children;

    public function __construct($id = null, array $children = [])
    {
        parent::__construct($id);
        $this->children = ChildEntity::collection($children);
    }

    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        $class->property($this->children)->asCollectionOf(ChildEntity::type());
    }
}
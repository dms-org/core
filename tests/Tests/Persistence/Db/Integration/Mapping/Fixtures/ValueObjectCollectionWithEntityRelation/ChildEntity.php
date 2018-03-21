<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObjectCollectionWithEntityRelation;

use Dms\Core\Model\EntityCollection;
use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\Entity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ChildEntity extends Entity
{
    /**
     * @var EntityCollection|ChildChildEntity[]
     */
    public $children;

    public function __construct(int $id = null, array $children = [])
    {
        parent::__construct($id);
        $this->children = ChildChildEntity::collection($children);
    }

    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        $class->property($this->children)->asType(ChildChildEntity::collectionType());
    }
}
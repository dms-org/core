<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObjectCollectionWithEntityRelation;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\ValueObject;
use Dms\Core\Model\ValueObjectCollection;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ChildValueObject extends ValueObject
{
    /**
     * @var ChildEntity
     */
    public $entity;

    /**
     * @var ValueObjectCollection|ChildChildValueObject[]
     */
    public $children;

    public function __construct(ChildEntity $childEntity, array $children =[])
    {
        parent::__construct();
        $this->entity = $childEntity;
        $this->children = ChildChildValueObject::collection($children);
    }


    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function define(ClassDefinition $class)
    {
        $class->property($this->entity)->asObject(ChildEntity::class);
        $class->property($this->children)->asType(ChildChildValueObject::collectionType());
    }
}
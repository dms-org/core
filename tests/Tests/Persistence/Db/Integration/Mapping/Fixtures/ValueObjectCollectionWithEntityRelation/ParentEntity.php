<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObjectCollectionWithEntityRelation;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\Entity;
use Dms\Core\Model\ValueObjectCollection;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ParentEntity extends Entity
{
    /**
     * @var ValueObjectCollection|ChildValueObject[]s
     */
    public $valueObjects;

    public function __construct(int $id = null, array $valueObjects = [])
    {
        parent::__construct($id);

        $this->valueObjects = ChildValueObject::collection($valueObjects);
    }

    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        $class->property($this->valueObjects)->asType(ChildValueObject::collectionType());
    }
}
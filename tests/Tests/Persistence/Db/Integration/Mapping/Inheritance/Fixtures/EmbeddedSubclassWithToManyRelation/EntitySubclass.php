<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Inheritance\Fixtures\EmbeddedSubclassWithToManyRelation;

use Dms\Core\Model\EntityCollection;
use Dms\Core\Model\IEntityCollection;
use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EntitySubclass extends RootEntity
{
    /**
     * @var IEntityCollection|ChildEntity[]
     */
    public $children;

    /**
     * EmbeddedObject constructor.
     *
     * @param int|null      $id
     * @param ChildEntity[] $children
     */
    public function __construct($id, array $children)
    {
        parent::__construct($id);
        $this->children = ChildEntity::collection($children);
    }


    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        $class->property($this->children)->asType(ChildEntity::collectionType());
    }
}
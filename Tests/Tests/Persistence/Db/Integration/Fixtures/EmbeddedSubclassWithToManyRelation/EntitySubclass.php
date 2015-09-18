<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\EmbeddedSubclassWithToManyRelation;

use Iddigital\Cms\Core\Model\EntityCollection;
use Iddigital\Cms\Core\Model\IEntityCollection;
use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Type\Builder\Type;

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
        $this->children = new EntityCollection(ChildEntity::class, $children);
    }


    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        $class->property($this->children)->asCollectionOf(Type::object(ChildEntity::class));
    }
}
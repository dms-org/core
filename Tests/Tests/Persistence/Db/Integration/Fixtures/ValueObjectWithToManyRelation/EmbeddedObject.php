<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\ValueObjectWithToManyRelation;

use Iddigital\Cms\Core\Model\EntityCollection;
use Iddigital\Cms\Core\Model\IEntityCollection;
use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\ValueObject;
use Iddigital\Cms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EmbeddedObject extends ValueObject
{
    /**
     * @var IEntityCollection|ChildEntity[]
     */
    public $children;

    /**
     * EmbeddedObject constructor.
     *
     * @param ChildEntity[] $children
     */
    public function __construct(array $children)
    {
        parent::__construct();
        $this->children = new EntityCollection(ChildEntity::class, $children);
    }


    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function define(ClassDefinition $class)
    {
        $class->property($this->children)->asCollectionOf(Type::object(ChildEntity::class));
    }
}
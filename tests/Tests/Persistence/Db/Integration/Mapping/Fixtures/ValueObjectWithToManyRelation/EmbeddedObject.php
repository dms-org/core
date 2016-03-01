<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObjectWithToManyRelation;

use Dms\Core\Model\EntityCollection;
use Dms\Core\Model\IEntityCollection;
use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\ValueObject;
use Dms\Core\Model\Type\Builder\Type;

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
        $this->children = ChildEntity::collection($children);
    }


    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function define(ClassDefinition $class)
    {
        $class->property($this->children)->asType(ChildEntity::collectionType());
    }
}
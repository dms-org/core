<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\ReadModel\Fixtures\LoadToManyIdRelation;

use Iddigital\Cms\Core\Model\EntityCollection;
use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\ReadModel;
use Iddigital\Cms\Core\Model\Type\Builder\Type;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\ToManyIdRelation\ChildEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ReadModelWithChildEntities extends ReadModel
{
    /**
     * @var EntityCollection|ChildEntity[]
     */
    public $children;

    /**
     * ReadModelWithLoadedToOneRelation constructor.
     *
     * @param ChildEntity[] $children
     */
    public function __construct(array $children = [])
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
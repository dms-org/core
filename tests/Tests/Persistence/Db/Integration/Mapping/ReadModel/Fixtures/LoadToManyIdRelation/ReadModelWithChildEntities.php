<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel\Fixtures\LoadToManyIdRelation;

use Dms\Core\Model\EntityCollection;
use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\ReadModel;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToManyIdRelation\ChildEntity;

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
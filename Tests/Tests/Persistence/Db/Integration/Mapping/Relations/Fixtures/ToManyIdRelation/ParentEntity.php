<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToManyIdRelation;

use Iddigital\Cms\Core\Model\EntityIdCollection;
use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\Entity;
use Iddigital\Cms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ParentEntity extends Entity
{
    /**
     * @var EntityIdCollection
     */
    public $childIds;

    /**
     * @param int|null $id
     * @param int[]    $childIds
     */
    public function __construct($id, array $childIds = [])
    {
        parent::__construct($id);
        $this->childIds = new EntityIdCollection($childIds);
    }

    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        $class->property($this->childIds)->asCollectionOf(Type::int());
    }
}
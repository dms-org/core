<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ManyToManyRelation\Bidirectional;

use Dms\Core\Model\EntityCollection;
use Dms\Core\Model\IEntityCollection;
use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\Entity;
use Dms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class OneEntity extends Entity
{
    /**
     * @var IEntityCollection|AnotherEntity[]
     */
    public $others;

    /**
     * SubEntity constructor.
     *
     * @param int|null        $id
     * @param AnotherEntity[] $others
     */
    public function __construct($id = null, array $others = [])
    {
        parent::__construct($id);
        $this->others = new EntityCollection(AnotherEntity::class);

        foreach ($others as $other) {
            $this->addOther($other);
        }
    }

    public function addOther(AnotherEntity $entity)
    {
        $this->others[] = $entity;
        $entity->ones[] = $this;
    }

    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        $class->property($this->others)->asCollectionOf(Type::object(AnotherEntity::class));
    }
}
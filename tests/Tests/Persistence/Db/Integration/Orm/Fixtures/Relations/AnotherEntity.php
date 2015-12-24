<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Orm\Fixtures\Relations;

use Dms\Core\Model\EntityCollection;
use Dms\Core\Model\IEntityCollection;
use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\Entity;
use Dms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class AnotherEntity extends Entity
{
    /**
     * @var IEntityCollection|OneEntity[]
     */
    public $ones;

    public function __construct($id = null, array $ones = [])
    {
        parent::__construct($id);
        $this->ones = new EntityCollection(OneEntity::class);

        foreach ($ones as $one) {
            $this->addOne($one);
        }
    }

    public function addOne(OneEntity $entity)
    {
        $this->ones[]     = $entity;
        $entity->others[] = $this;
    }

    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        $class->property($this->ones)->asCollectionOf(Type::object(OneEntity::class));
    }
}
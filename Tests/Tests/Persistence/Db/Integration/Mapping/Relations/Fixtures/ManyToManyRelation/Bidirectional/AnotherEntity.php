<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ManyToManyRelation\Bidirectional;

use Iddigital\Cms\Core\Model\EntityCollection;
use Iddigital\Cms\Core\Model\IEntityCollection;
use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\Entity;
use Iddigital\Cms\Core\Model\Type\Builder\Type;

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
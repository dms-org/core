<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ManyToManyRelation;

use Iddigital\Cms\Core\Model\EntityCollection;
use Iddigital\Cms\Core\Model\IEntityCollection;
use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\Entity;
use Iddigital\Cms\Core\Model\Type\Builder\Type;

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
        $this->others = new EntityCollection(AnotherEntity::class, $others);
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
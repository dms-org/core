<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\SelfReferencing\ManyToManyRelation;

use Dms\Core\Model\EntityCollection;
use Dms\Core\Model\IEntityCollection;
use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\Entity;
use Dms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class RecursiveEntity extends Entity
{
    /**
     * @var IEntityCollection|RecursiveEntity[]
     */
    public $parents;

    /**
     * RecursiveEntity constructor.
     *
     * @param int|null          $id
     * @param RecursiveEntity[] $parents
     */
    public function __construct($id = null, array $parents = [])
    {
        parent::__construct($id);
        $this->parents = new EntityCollection(__CLASS__, $parents);
    }


    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        $class->property($this->parents)->asCollectionOf(Type::object(__CLASS__));
    }
}
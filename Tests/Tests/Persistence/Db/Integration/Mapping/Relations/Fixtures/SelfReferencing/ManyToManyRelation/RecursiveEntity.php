<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\SelfReferencing\ManyToManyRelation;

use Iddigital\Cms\Core\Model\EntityCollection;
use Iddigital\Cms\Core\Model\IEntityCollection;
use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\Entity;
use Iddigital\Cms\Core\Model\Type\Builder\Type;

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
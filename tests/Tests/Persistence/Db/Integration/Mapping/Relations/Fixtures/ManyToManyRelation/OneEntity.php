<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ManyToManyRelation;

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
        $this->others = AnotherEntity::collection($others);
    }

    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        $class->property($this->others)->asType(AnotherEntity::collectionType());
    }
}
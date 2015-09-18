<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\ToOneIdRelation;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\Entity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ParentEntity extends Entity
{
    /**
     * @var int|null
     */
    public $childId;

    /**
     * ParentEntity constructor.
     *
     * @param int|null $id
     * @param int|null $childId
     */
    public function __construct($id, $childId)
    {
        parent::__construct($id);
        $this->childId = $childId;
    }


    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        $class->property($this->childId)->nullable()->asInt();
    }
}
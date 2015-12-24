<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ManyToOneIdRelation;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\Entity;

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
     * @param null     $id
     * @param int|null $childId
     */
    public function __construct($id = null, $childId = null)
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
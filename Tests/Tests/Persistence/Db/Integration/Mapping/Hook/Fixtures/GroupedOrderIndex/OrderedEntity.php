<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Hook\Fixtures\GroupedOrderIndex;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\Entity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class OrderedEntity extends Entity
{
    /**
     * @var string
     */
    public $group;

    /**
     * @var int|null
     */
    public $orderIndex;

    /**
     * @inheritDoc
     */
    public function __construct($id = null, $group, $orderIndex = null)
    {
        parent::__construct($id);
        $this->group = $group;
        $this->orderIndex = $orderIndex;
    }

    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        $class->property($this->group)->asString();
        $class->property($this->orderIndex)->nullable()->asInt();
    }
}
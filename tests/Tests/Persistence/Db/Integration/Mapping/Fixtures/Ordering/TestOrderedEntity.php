<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\Ordering;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\Entity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TestOrderedEntity extends Entity
{
    const GROUP = 'group';
    const ORDER = 'order';

    /**
     * @var TestGroupEnum
     */
    public $group;

    /**
     * @var int
     */
    public $order;

    /**
     * TestOrderedEntity constructor.
     *
     * @param TestGroupEnum $group
     */
    public function __construct(TestGroupEnum $group)
    {
        parent::__construct();
        $this->group = $group;
    }

    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        $class->property($this->group)->asObject(TestGroupEnum::class);

        $class->property($this->order)->asInt();
    }
}
<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Hook\Fixtures\GlobalOrderIndex;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\Entity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class OrderedEntity extends Entity
{
    /**
     * @var int|null
     */
    public $orderIndex;

    /**
     * @inheritDoc
     */
    public function __construct($id = null, $orderIndex = null)
    {
        parent::__construct($id);
        $this->orderIndex = $orderIndex;
    }

    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        $class->property($this->orderIndex)->nullable()->asInt();
    }
}
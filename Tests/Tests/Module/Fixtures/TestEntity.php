<?php

namespace Dms\Core\Tests\Module\Fixtures;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\Entity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TestEntity extends Entity
{
    /**
     * @var string
     */
    public $name;

    /**
     * @param int|null $id
     * @param string   $name
     */
    public function __construct($id, $name)
    {
        parent::__construct($id);
        $this->name = $name;
    }

    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        $class->property($this->name)->asString();
    }
}
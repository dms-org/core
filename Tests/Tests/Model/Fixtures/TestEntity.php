<?php

namespace Iddigital\Cms\Core\Tests\Model\Fixtures;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\Entity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TestEntity extends Entity
{
    /**
     * @var string
     */
    public $prop;

    /**
     * @var SubObject|null
     */
    public $object;

    /**
     * @inheritDoc
     */
    public function __construct($id = null, $prop = '', SubObject $object = null)
    {
        parent::__construct($id);
        $this->prop = $prop;
        $this->object = $object;
    }

    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        $class->property($this->prop)->asString();
        $class->property($this->object)->nullable()->asObject(SubObject::class);
    }

    /**
     * @return string
     */
    public function getProp()
    {
        return $this->prop;
    }
}
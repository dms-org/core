<?php

namespace Dms\Core\Tests\Model\Fixtures;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\Entity;
use Dms\Core\Model\ValueObjectCollection;

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
     * @var ValueObjectCollection|SubObject[]
     */
    public $objects;

    /**
     * @inheritDoc
     */
    public function __construct($id = null, $prop = '', SubObject $object = null, array $objects = [])
    {
        parent::__construct($id);
        $this->prop    = $prop;
        $this->object  = $object;
        $this->objects = SubObject::collection($objects);
    }

    /**
     * @param array $objects
     *
     * @return TestEntity
     */
    public static function withSubObjects(array $objects)
    {
        return new self(null, '', null, $objects);
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
        $class->property($this->objects)->asCollectionOf(SubObject::type());
    }

    /**
     * @return string
     */
    public function getProp()
    {
        return $this->prop;
    }
}
<?php

namespace Dms\Core\Tests\Form\Field\Fixtures;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\Entity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TestEntity extends Entity
{
    const NAME = 'name';
    const VALUE_OBJECT = 'valueObject';

    /**
     * @var string
     */
    public $name;

    /**
     * @var TestValueObject
     */
    public $valueObject;

    /**
     * TestEntity constructor.
     *
     * @param int             $id
     * @param string          $name
     * @param TestValueObject $valueObject
     */
    public function __construct(int $id, string $name, TestValueObject $valueObject)
    {
        parent::__construct($id);
        $this->name        = $name;
        $this->valueObject = $valueObject;
    }

    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        $class->property($this->name)->asString();
        $class->property($this->valueObject)->asObject(TestValueObject::class);
    }
}
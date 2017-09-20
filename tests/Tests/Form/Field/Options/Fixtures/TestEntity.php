<?php

namespace Dms\Core\Tests\Form\Field\Options\Fixtures;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\Entity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TestEntity extends Entity
{
    const NAME = 'name';

    /**
     * @var string
     */
    public $name;

    /**
     * TestEntity constructor.
     *
     * @param int             $id
     * @param string          $name
     */
    public function __construct(int $id, string $name)
    {
        parent::__construct($id);
        $this->name        = $name;
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
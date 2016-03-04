<?php

namespace Dms\Core\Tests\Common\Crud\Action\Object\Mapping\Fixtures;

use Dms\Core\Model\EntityCollection;
use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\Entity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TestEntity extends Entity
{

    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {

    }

    /**
     * @param $id
     *
     * @return TestEntity
     */
    public static function withId($id)
    {
        return new self($id);
    }

    /**
     * @return EntityCollection|static[]
     */
    public static function getTestCollection()
    {
        return static::collection([
                new self(1),
                new self(2),
                new self(3),
        ]);
    }
}
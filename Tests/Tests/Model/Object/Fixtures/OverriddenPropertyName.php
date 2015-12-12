<?php

namespace Iddigital\Cms\Core\Tests\Model\Object\Fixtures;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\TypedObject;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class OverriddenPropertyNameBase extends TypedObject
{
    /**
     * @var bool
     */
    protected $prop;

    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function define(ClassDefinition $class)
    {
        $class->property($this->prop)->asBool();
    }
}

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class OverriddenPropertyName extends OverriddenPropertyNameBase
{
    /**
     * @var bool
     */
    protected $prop;

    /**
     * @param bool $prop
     *
     * @return void
     */
    public function setProp($prop)
    {
        $this->prop = $prop;
    }

    /**
     * @return bool
     */
    public function getProp()
    {
        return $this->prop;
    }
}
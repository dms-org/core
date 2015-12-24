<?php

namespace Dms\Core\Tests\Model\Object\Fixtures;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\TypedObject;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PropertyAccessibilities extends TypedObject
{
    /**
     * @var bool
     */
    public $public;

    /**
     * @var bool
     */
    protected $protected;

    /**
     * @var bool
     */
    private $private;

    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function define(ClassDefinition $class)
    {
        $class->property($this->public)->asBool();

        $class->property($this->protected)->asBool();

        $class->property($this->private)->asBool();
    }

    public static function build()
    {
        return self::construct();
    }
}
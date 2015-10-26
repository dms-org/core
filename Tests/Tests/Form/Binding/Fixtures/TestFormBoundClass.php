<?php

namespace Iddigital\Cms\Core\Tests\Form\Binding\Fixtures;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\TypedObject;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TestFormBoundClass extends TypedObject
{
    /**
     * @var string
     */
    public $string;

    /**
     * @var int
     */
    public $int;

    /**
     * @var bool
     */
    public $bool;

    /**
     * TestFormBoundClass constructor.
     *
     * @param string $string
     * @param int    $int
     * @param bool   $bool
     */
    public function __construct($string, $int, $bool)
    {
        parent::__construct();
        $this->string = $string;
        $this->int    = $int;
        $this->bool   = $bool;
    }

    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function define(ClassDefinition $class)
    {
        $class->property($this->string)->asString();

        $class->property($this->int)->asInt();

        $class->property($this->bool)->asBool();
    }

    /**
     * @return int
     */
    public function getInt()
    {
        return $this->int;
    }

    /**
     * @param int $int
     */
    public function setInt($int)
    {
        $this->int = $int;
    }
}
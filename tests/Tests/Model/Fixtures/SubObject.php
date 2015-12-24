<?php

namespace Dms\Core\Tests\Model\Fixtures;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\ValueObject;
use Dms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class SubObject extends ValueObject
{
    /**
     * @var string
     */
    public $prop;

    /**
     * @var int
     */
    public $number;

    /**
     * @var int[]
     */
    public $numbers;

    /**
     * @inheritDoc
     */
    public function __construct($prop = '', $number = 0, $numbers = [])
    {
        parent::__construct();
        $this->prop = $prop;
        $this->numbers = $numbers;
        $this->number = $number;
    }

    /**
     * Defines the structure of this value object.
     *
     * @param ClassDefinition $class
     */
    protected function define(ClassDefinition $class)
    {
        $class->property($this->prop)->asString();
        $class->property($this->number)->asInt();
        $class->property($this->numbers)->asArrayOf(Type::int());
    }
}
<?php

namespace Iddigital\Cms\Core\Tests\Model\Fixtures;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\ValueObject;

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
     * @inheritDoc
     */
    public function __construct($prop = '')
    {
        parent::__construct();
        $this->prop = $prop;
    }

    /**
     * Defines the structure of this value object.
     *
     * @param ClassDefinition $class
     */
    protected function define(ClassDefinition $class)
    {
        $class->property($this->prop)->asString();
    }
}
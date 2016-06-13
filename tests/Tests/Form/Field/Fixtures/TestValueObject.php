<?php

namespace Dms\Core\Tests\Form\Field\Fixtures;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\Entity;
use Dms\Core\Model\Object\ValueObject;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TestValueObject extends ValueObject
{
    const STRING = 'string';

    /**
     * @var string
     */
    public $string;

    /**
     * TestValueObject constructor.
     *
     * @param string $string
     */
    public function __construct(string $string)
    {
        parent::__construct();
        $this->string = $string;
    }

    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function define(ClassDefinition $class)
    {
        $class->property($this->string)->asString();
    }
}
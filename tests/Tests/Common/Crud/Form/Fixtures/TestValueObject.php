<?php

namespace Dms\Core\Tests\Common\Crud\Form\Fixtures;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\ValueObject;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TestValueObject extends ValueObject
{
    const STRING = 'string';
    const INT = 'int';

    /**
     * @var string
     */
    public $string;

    /**
     * @var int
     */
    public $int;

    /**
     * TestValueObject constructor.
     *
     * @param string $string
     * @param int    $int
     */
    public function __construct(string $string, int $int)
    {
        parent::__construct();
        $this->string = $string;
        $this->int    = $int;
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
    }
}
<?php

namespace Iddigital\Cms\Core\Tests\Table\DataSource\Fixtures;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\TypedObject;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TestPerson extends TypedObject
{
    /**
     * @var string
     */
    public $firstName;

    /**
     * @var string
     */
    public $lastName;

    /**
     * @var int
     */
    public $age;

    /**
     * TestPerson constructor.
     *
     * @param string $firstName
     * @param string $lastName
     * @param int    $age
     */
    public function __construct($firstName, $lastName, $age)
    {
        parent::__construct();
        $this->firstName = $firstName;
        $this->lastName  = $lastName;
        $this->age       = $age;
    }

    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function define(ClassDefinition $class)
    {
        $class->property($this->firstName)->asString();
        $class->property($this->lastName)->asString();
        $class->property($this->age)->asInt();
    }
}
<?php

namespace Iddigital\Cms\Core\Tests\Model\Object\Fixtures;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\TypedObject;
use Iddigital\Cms\Core\Model\Type\Builder\Type as Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Person extends TypedObject
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
     * @var \DateTime
     */
    public $dateOfBirth;

    /**
     * @var bool
     */
    public $married;

    /**
     * @var string
     */
    public $emailAddress;

    /**
     * Person constructor.
     *
     * @param string    $firstName
     * @param string    $lastName
     * @param \DateTime $dateOfBirth
     * @param bool      $married
     * @param string    $emailAddress
     */
    public function __construct($firstName, $lastName, \DateTime $dateOfBirth, $married, $emailAddress)
    {
        parent::__construct();
        $this->firstName    = $firstName;
        $this->lastName     = $lastName;
        $this->dateOfBirth  = $dateOfBirth;
        $this->married      = $married;
        $this->emailAddress = $emailAddress;
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

        $class->property($this->dateOfBirth)->asObject(\DateTime::class);

        $class->property($this->married)->asBool();

        $class->property($this->emailAddress)->asType(Type::string()->nullable());
    }
}
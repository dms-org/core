<?php

namespace Iddigital\Cms\Core\Tests\Common\Crud\Modules\Fixtures\Complex\Domain;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\Entity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class Person extends Entity
{
    const COMING_OF_AGE = 18;

    const FIRST_NAME = 'firstName';
    const LAST_NAME = 'lastName';
    const AGE = 'age';

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
     * Person constructor.
     *
     * @param int|null $id
     * @param string   $firstName
     * @param string   $lastName
     * @param int      $age
     *
     * @throws InvalidArgumentException
     */
    public function __construct($id, $firstName, $lastName, $age)
    {
        parent::__construct($id);
        $this->firstName = $firstName;
        $this->lastName  = $lastName;
        $this->age       = $age;

        if (!$this->isValidAge($age)) {
            throw InvalidArgumentException::format('Invalid age %d for class %s', $age, get_class($this));
        }
    }

    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        $class->property($this->firstName)->asString();
        $class->property($this->lastName)->asString();
        $class->property($this->age)->asInt();
    }

    /**
     * @param int $age
     *
     * @return bool
     */
    abstract protected function isValidAge($age);

    /**
     * @return string
     */
    public function getFullName()
    {
        return $this->firstName . ' ' . $this->lastName;
    }
}
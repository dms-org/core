<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\Entity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Alias extends Entity
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
     * @inheritDoc
     */
    public function __construct($id = null, $firstName, $lastName)
    {
        parent::__construct($id);
        $this->firstName = $firstName;
        $this->lastName  = $lastName;
    }

    public static function create($firstName, $lastName)
    {
        return new self(null, $firstName, $lastName);
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
    }
}
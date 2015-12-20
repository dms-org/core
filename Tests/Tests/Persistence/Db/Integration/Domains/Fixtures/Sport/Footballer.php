<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Sport;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\Entity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Footballer extends Player
{
    /**
     * @var string
     */
    public $club;

    /**
     * @inheritDoc
     */
    public function __construct($id, $name, $club)
    {
        parent::__construct($id, $name);
        $this->club = $club;
    }

    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        parent::defineEntity($class);

        $class->property($this->club)->asString();
    }
}
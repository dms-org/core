<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Sport;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\Entity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class Player extends Entity
{
    /**
     * @var string
     */
    public $name;

    /**
     * @inheritDoc
     */
    public function __construct($id, $name)
    {
        parent::__construct($id);
        $this->name = $name;
    }

    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        $class->property($this->name)->asString();
    }
}
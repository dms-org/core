<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Sport;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\Entity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Cricketer extends Player
{
    /**
     * @var int
     */
    public $battingAverage;

    /**
     * @inheritDoc
     */
    public function __construct($id, $name, $battingAverage)
    {
        parent::__construct($id, $name);
        $this->battingAverage = $battingAverage;
    }

    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        parent::defineEntity($class);

        $class->property($this->battingAverage)->asInt();
    }
}
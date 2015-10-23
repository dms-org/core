<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Sport;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\Entity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Bowler extends Cricketer
{
    /**
     * @var int
     */
    public $bowlingAverage;

    /**
     * @inheritDoc
     */
    public function __construct($id, $name, $battingAverage, $bowlingAverage)
    {
        parent::__construct($id, $name, $battingAverage);
        $this->bowlingAverage = $bowlingAverage;
    }

    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        parent::defineEntity($class);

        $class->property($this->bowlingAverage)->asInt();
    }
}
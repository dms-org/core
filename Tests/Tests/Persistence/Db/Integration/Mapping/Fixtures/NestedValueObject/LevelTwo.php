<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\NestedValueObject;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\ValueObject;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class LevelTwo extends ValueObject
{
    /**
     * @var LevelThree
     */
    public $three;

    /**
     * LevelTwo constructor.
     *
     * @param LevelThree $three
     */
    public function __construct(LevelThree $three)
    {
        parent::__construct();
        $this->three = $three;
    }

    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function define(ClassDefinition $class)
    {
        $class->property($this->three)->asObject(LevelThree::class);
    }
}
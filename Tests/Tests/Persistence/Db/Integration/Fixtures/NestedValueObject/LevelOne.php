<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\NestedValueObject;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\ValueObject;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class LevelOne extends ValueObject
{
    /**
     * @var LevelTwo
     */
    public $two;

    /**
     * LevelOne constructor.
     *
     * @param LevelTwo $two
     */
    public function __construct(LevelTwo $two)
    {
        parent::__construct();
        $this->two = $two;
    }


    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function define(ClassDefinition $class)
    {
        $class->property($this->two)->asObject(LevelTwo::class);
    }
}
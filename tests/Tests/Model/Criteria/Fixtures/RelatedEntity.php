<?php

namespace Dms\Core\Tests\Model\Criteria\Fixtures;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\Entity;
use Dms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class RelatedEntity extends Entity
{
    /**
     * @var string
     */
    public $prop;

    /**
     * @var int
     */
    public $number;

    /**
     * @var int[]
     */
    public $numbers;

    /**
     * RelatedEntity constructor.
     *
     * @param string $prop
     * @param int       $number
     * @param \int[] $numbers
     */
    public function __construct($prop, $number, array $numbers)
    {
        parent::__construct();
        $this->prop    = $prop;
        $this->number = $number;
        $this->numbers = $numbers;
    }

    /**
     * @inheritDoc
     */
    protected function defineEntity(ClassDefinition $class)
    {
        $class->property($this->prop)->asString();

        $class->property($this->number)->asInt();

        $class->property($this->numbers)->asArrayOf(Type::int());
    }


}
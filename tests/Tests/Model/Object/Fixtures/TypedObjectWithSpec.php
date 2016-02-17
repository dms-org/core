<?php

namespace Dms\Core\Tests\Model\Object\Fixtures;

use Dms\Core\Model\Criteria\SpecificationDefinition;
use Dms\Core\Model\ISpecification;
use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\TypedObject;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TypedObjectWithSpec extends TypedObject
{
    /**
     * @var int
     */
    public $prop;

    /**
     * TypedObjectWithSpec constructor.
     *
     * @param int $prop
     */
    public function __construct($prop)
    {
        parent::__construct();
        $this->prop = $prop;
    }


    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function define(ClassDefinition $class)
    {
        $class->property($this->prop)->asInt();
    }

    /**
     * @param int $int
     *
     * @return ISpecification
     */
    public static function testWherePropEquals(int $int) : ISpecification
    {
        return self::specification(function (SpecificationDefinition $match) use ($int) {
            $match->where('prop', '=', $int);
        });
    }
}
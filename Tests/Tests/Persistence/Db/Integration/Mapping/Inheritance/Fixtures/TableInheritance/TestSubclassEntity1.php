<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Inheritance\Fixtures\TableInheritance;

use Dms\Core\Model\Object\ClassDefinition;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TestSubclassEntity1 extends TestSuperclassEntity
{
    /**
     * @var int
     */
    public $subClass1Prop = 1;

    /**
     * @param int|null $id
     * @param string   $baseProp
     * @param int      $subClass1Prop
     */
    public function __construct($id, $baseProp, $subClass1Prop)
    {
        parent::__construct($id, $baseProp);
        $this->subClass1Prop = $subClass1Prop;
    }

    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        parent::defineEntity($class);
        $class->property($this->subClass1Prop)->asInt();
    }
}
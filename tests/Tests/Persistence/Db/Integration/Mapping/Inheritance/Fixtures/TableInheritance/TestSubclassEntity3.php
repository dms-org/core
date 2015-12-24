<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Inheritance\Fixtures\TableInheritance;

use Dms\Core\Model\Object\ClassDefinition;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TestSubclassEntity3 extends TestSubclassEntity1
{
    /**
     * @var string
     */
    public $subClass3Prop = '';

    /**
     * @param int|null $id
     * @param string   $baseProp
     * @param int      $subClass1Prop
     * @param string   $subClass3Prop
     */
    public function __construct($id, $baseProp, $subClass1Prop, $subClass3Prop)
    {
        parent::__construct($id, $baseProp, $subClass1Prop);
        $this->subClass3Prop = $subClass3Prop;
    }


    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        parent::defineEntity($class);
        $class->property($this->subClass3Prop)->asString();
    }
}
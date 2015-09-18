<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\TableInheritance;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TestSubclassEntity2 extends TestSuperclassEntity
{
    /**
     * @var int
     */
    public $subClass2Prop = 2;

    /**
     * @var bool
     */
    public $subClass2Prop2 = true;

    /**
     * @param int|null $id
     * @param string   $baseProp
     * @param int         $subClass2Prop
     * @param bool         $subClass2Prop2
     */
    public function __construct($id, $baseProp, $subClass2Prop, $subClass2Prop2)
    {
        parent::__construct($id, $baseProp);
        $this->subClass2Prop  = $subClass2Prop;
        $this->subClass2Prop2 = $subClass2Prop2;
    }

    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        parent::defineEntity($class);
        $class->property($this->subClass2Prop)->asInt();
        $class->property($this->subClass2Prop2)->asBool();
    }
}
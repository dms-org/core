<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Inheritance\Fixtures\TableInheritance;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\Entity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TestSuperclassEntity extends Entity
{
    /**
     * @var string
     */
    public $baseProp = 'foo';

    /**
     * TestAbstractEntity constructor.
     *
     * @param int|null $id
     * @param string   $baseProp
     */
    public function __construct($id, $baseProp)
    {
        parent::__construct($id);
        $this->baseProp = $baseProp;
    }

    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        $class->property($this->baseProp)->asString();
    }
}
<?php

namespace Dms\Core\Tests\Table\DataSource\Fixtures;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\TypedObject;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TestParentPerson extends TypedObject
{
    /**
     * @var TestPerson
     */
    public $child;

    /**
     * TestParentPerson constructor.
     *
     * @param TestPerson $child
     */
    public function __construct(TestPerson $child)
    {
        parent::__construct();
        $this->child = $child;
    }


    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function define(ClassDefinition $class)
    {
        $class->property($this->child)->asObject(TestPerson::class);
    }
}
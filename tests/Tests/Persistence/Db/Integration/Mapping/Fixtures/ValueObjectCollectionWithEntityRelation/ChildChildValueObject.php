<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObjectCollectionWithEntityRelation;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\ValueObject;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ChildChildValueObject extends ValueObject
{
    /**
     * @var string
     */
    public $data;

    public function __construct(string $data = '')
    {
        parent::__construct();
        $this->data = $data;
    }

    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function define(ClassDefinition $class)
    {
        $class->property($this->data)->asString();
    }
}
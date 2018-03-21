<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObjectCollectionWithEntityRelation;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\Entity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ChildChildEntity extends Entity
{
    /**
     * @var string
     */
    public $data;

    public function __construct(int $id = null, string $data = '')
    {
        parent::__construct($id);
        $this->data = $data;
    }

    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        $class->property($this->data)->asString();
    }
}
<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Orm\Fixtures\RelatedId;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\Entity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ChildEntity extends Entity
{
    /**
     * @var int
     */
    public $parentId;

    /**
     * @var string|null
     */
    public $data;

    public function __construct($id, $parentId, $data = null)
    {
        parent::__construct($id);
        $this->parentId = $parentId;
        $this->data = $data;
    }

    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        $class->property($this->parentId)->asInt();
        $class->property($this->data)->nullable()->asString();
    }
}
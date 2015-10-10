<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToOneRelation;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\Entity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ParentEntity extends Entity
{
    /**
     * @var SubEntity
     */
    public $child;

    /**
     * @inheritDoc
     */
    public function __construct($id = null, SubEntity $child = null)
    {
        parent::__construct($id);
        if ($child) {
            $this->child = $child;
        }
    }


    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        $class->property($this->child)->asObject(SubEntity::class);
    }
}
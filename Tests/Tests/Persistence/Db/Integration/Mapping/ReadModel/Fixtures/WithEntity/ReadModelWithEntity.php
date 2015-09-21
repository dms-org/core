<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel\Fixtures\WithEntity;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\ReadModel;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToOneIdRelation\ParentEntity;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToOneIdRelation\SubEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ReadModelWithEntity extends ReadModel
{
    /**
     * @var ParentEntity
     */
    public $parent;

    /**
     * @var SubEntity
     */
    public $child;

    /**
     * ReadModelWithLoadedToOneRelation constructor.
     *
     * @param ParentEntity $parent
     * @param SubEntity    $child
     */
    public function __construct(ParentEntity $parent, SubEntity $child)
    {
        parent::__construct();
        $this->parent = $parent;
        $this->child  = $child;
    }

    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function define(ClassDefinition $class)
    {
        $class->property($this->parent)->asObject(ParentEntity::class);
        $class->property($this->child)->asObject(SubEntity::class);
    }
}
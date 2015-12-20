<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Locking\Fixtures\VersionedToManyRelation;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\Entity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ChildEntity extends Entity
{
    /**
     * @var int|null
     */
    public $version;

    /**
     * SubEntity constructor.
     *
     * @param int|null $id
     * @param int  $version
     */
    public function __construct($id = null, $version = null)
    {
        parent::__construct($id);
        $this->version = $version;
    }

    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        $class->property($this->version)->nullable()->asInt();
    }
}
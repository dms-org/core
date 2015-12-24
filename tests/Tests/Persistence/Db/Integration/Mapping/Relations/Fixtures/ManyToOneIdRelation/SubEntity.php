<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ManyToOneIdRelation;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\Entity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class SubEntity extends Entity
{
    /**
     * @var int
     */
    public $val;

    /**
     * SubEntity constructor.
     *
     * @param int|null $id
     * @param int      $val
     */
    public function __construct($id, $val)
    {
        parent::__construct($id);
        $this->val = $val;
    }

    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        $class->property($this->val)->asInt();
    }
}
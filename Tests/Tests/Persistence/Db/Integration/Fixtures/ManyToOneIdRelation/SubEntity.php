<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\ManyToOneIdRelation;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\Entity;

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
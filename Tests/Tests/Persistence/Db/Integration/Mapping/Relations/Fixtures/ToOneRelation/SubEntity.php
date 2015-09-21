<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToOneRelation;

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
     * @param int  $val
     * @param int|null $id
     */
    public function __construct($val, $id = null)
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
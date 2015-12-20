<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToOneRelation\Polymorphic;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToOneRelation\SubEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class SubEntitySubclass extends SubEntity
{
    /**
     * @var string
     */
    public $sub;

    /**
     * SubEntity constructor.
     *
     * @param int      $val
     * @param string   $sub
     */
    public function __construct($val, $sub)
    {
        parent::__construct($val);
        $this->sub = $sub;
    }

    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        parent::defineEntity($class);

        $class->property($this->sub)->asString();
    }
}
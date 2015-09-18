<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\ToOneRelation\Polymorphic;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\ToOneRelation\SubEntity;

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
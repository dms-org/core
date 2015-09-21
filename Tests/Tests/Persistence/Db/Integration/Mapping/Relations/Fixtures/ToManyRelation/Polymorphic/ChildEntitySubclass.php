<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToManyRelation\Polymorphic;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToManyRelation\ChildEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ChildEntitySubclass extends ChildEntity
{
    /**
     * @var string
     */
    public $sub;

    /**
     * SubEntity constructor.
     *
     * @param int|null $id
     * @param int      $val
     * @param string   $sub
     */
    public function __construct($id, $val, $sub)
    {
        parent::__construct($id, $val);
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
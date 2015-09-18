<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\ManyToManyRelation\Polymorphic;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\ManyToManyRelation\AnotherEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class AnotherEntitySubclass extends AnotherEntity
{
    /**
     * @var bool
     */
    public $data;

    public function __construct($id = null, $val, $data = false)
    {
        parent::__construct($id, $val);
        $this->data = $data;
    }

    /**
     * @inheritDoc
     */
    protected function defineEntity(ClassDefinition $class)
    {
        parent::defineEntity($class);

        $class->property($this->data)->asBool();
    }


}
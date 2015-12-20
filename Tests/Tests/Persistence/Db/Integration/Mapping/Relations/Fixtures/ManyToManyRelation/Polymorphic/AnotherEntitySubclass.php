<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ManyToManyRelation\Polymorphic;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ManyToManyRelation\AnotherEntity;

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
<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\CustomLoader;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\Entity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CustomLoadedEntity extends Entity
{
    /**
     * @var int
     */
    public $integer;

    /**
     * @inheritDoc
     */
    public function __construct($id = null, $integer = null)
    {
        parent::__construct($id);
        $this->integer = $integer;
    }


    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        $class->property($this->integer)->asInt();
    }
}
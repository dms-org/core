<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\PropertyConverters;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\Entity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ConvertedPropertyEntity extends Entity
{
    /**
     * @var int
     */
    public $integer;

    /**
     * @inheritDoc
     */
    public function __construct($id = null, $integer)
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
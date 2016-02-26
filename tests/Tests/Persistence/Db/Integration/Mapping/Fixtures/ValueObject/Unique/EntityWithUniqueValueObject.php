<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObject\Unique;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\Entity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EntityWithUniqueValueObject extends Entity
{
    /**
     * @var EmbeddedUniqueValueObject
     */
    public $valueObject;

    /**
     * EntityWithUniqueValueObject constructor.
     *
     * @param int                       $id
     * @param EmbeddedUniqueValueObject $valueObject
     */
    public function __construct(int $id = null, EmbeddedUniqueValueObject $valueObject)
    {
        parent::__construct($id);
        $this->valueObject = $valueObject;
    }

    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        $class->property($this->valueObject)->asObject(EmbeddedUniqueValueObject::class);
    }
}
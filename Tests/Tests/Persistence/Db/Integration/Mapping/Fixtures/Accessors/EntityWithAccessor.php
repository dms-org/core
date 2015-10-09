<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\Accessors;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\Entity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EntityWithAccessor extends Entity
{
    /**
     * @var string
     */
    private $value;

    /**
     * PropertyTypesEntity constructor.
     *
     * @param int|null $id
     * @param string   $value
     */
    public function __construct($id, $value)
    {
        parent::__construct($id);
        $this->setValue($value);
    }

    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        $class->property($this->value)->asString();
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return substr($this->value, strlen('foo'));
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = 'foo' . $value;
    }
}
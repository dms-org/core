<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\MutableValueObject;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\Entity;
use Dms\Core\Model\Object\ValueObject;


/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EntityWithValueObject extends Entity
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var EmbeddedMoneyObject
     */
    public $money;

    /**
     * @var EmbeddedMoneyObject
     */
    public $prefixedMoney;

    /**
     * @var EmbeddedMoneyObject|null
     */
    public $nullableMoney;

    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        $class->property($this->name)->asString();
        $class->property($this->money)->asObject(EmbeddedMoneyObject::class);
        $class->property($this->prefixedMoney)->asObject(EmbeddedMoneyObject::class);
        $class->property($this->nullableMoney)->nullable()->asObject(EmbeddedMoneyObject::class);
    }
}
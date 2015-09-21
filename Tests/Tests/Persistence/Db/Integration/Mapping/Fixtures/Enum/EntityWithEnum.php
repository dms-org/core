<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\Enum;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\Entity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EntityWithEnum extends Entity
{
    /**
     * @var StatusEnum
     */
    public $status;

    /**
     * @var StatusEnum|null
     */
    public $nullableStatus;

    /**
     * @var GenderEnum
     */
    public $gender;

    /**
     * @var GenderEnum|null
     */
    public $nullableGender;


    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        $class->property($this->status)->asObject(StatusEnum::class);

        $class->property($this->nullableStatus)->nullable()->asObject(StatusEnum::class);

        $class->property($this->gender)->asObject(GenderEnum::class);

        $class->property($this->nullableGender)->nullable()->asObject(GenderEnum::class);
    }
}
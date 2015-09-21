<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\Types;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\Entity;
use Iddigital\Cms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TypesEntity extends Entity
{
    /**
     * @var null
     */
    public $null;

    /**
     * @var int
     */
    public $int;

    /**
     * @var string
     */
    public $string;

    /**
     * @var float
     */
    public $float;

    /**
     * @var bool
     */
    public $bool;

    /**
     * @var \DateTimeImmutable
     */
    public $date;

    /**
     * @var \DateTimeImmutable
     */
    public $time;

    /**
     * @var \DateTimeImmutable
     */
    public $datetime;

    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        $class->property($this->null)->asType(Type::null());

        $class->property($this->int)->asInt();

        $class->property($this->string)->asString();

        $class->property($this->float)->asFloat();

        $class->property($this->bool)->asBool();

        $class->property($this->date)->asObject(\DateTimeImmutable::class);

        $class->property($this->time)->asObject(\DateTimeImmutable::class);

        $class->property($this->datetime)->asObject(\DateTimeImmutable::class);
    }
}
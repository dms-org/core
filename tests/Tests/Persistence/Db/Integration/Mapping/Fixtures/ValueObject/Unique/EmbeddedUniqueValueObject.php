<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObject\Unique;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\ValueObject;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EmbeddedUniqueValueObject extends ValueObject
{
    /**
     * @var int
     */
    public $int;

    /**
     * EmbeddedUniqueValueObject constructor.
     *
     * @param int $int
     */
    public function __construct($int)
    {
        parent::__construct();
        $this->int = $int;
    }


    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function define(ClassDefinition $class)
    {
        $class->property($this->int)->asInt();
    }
}
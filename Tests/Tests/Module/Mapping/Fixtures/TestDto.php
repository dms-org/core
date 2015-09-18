<?php

namespace Iddigital\Cms\Core\Tests\Module\Mapping\Fixtures;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\DataTransferObject;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TestDto extends DataTransferObject
{
    /**
     * @var bool
     */
    private $bool;

    /**
     * TestDto constructor.
     *
     * @param bool $bool
     */
    public function __construct($bool)
    {
       // parent::__construct();
        $this->bool = $bool;
    }


    public static function from($bool)
    {
        $self = self::construct();
        $self->bool = $bool;

        return $self;
    }

    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function define(ClassDefinition $class)
    {
        $class->property($this->bool)->asBool();
    }
}
<?php

namespace Iddigital\Cms\Core\Common\Crud\Dream\Complex;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\ValueObject;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Money extends ValueObject
{
    /**
     * @var int
     */
    protected $cents;

    /**
     * Money constructor.
     *
     * @param int $cents
     */
    public function __construct($cents)
    {
        parent::__construct();
        $this->cents = $cents;
    }

    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function define(ClassDefinition $class)
    {
        $class->property($this->cents)->asInt();
    }
}
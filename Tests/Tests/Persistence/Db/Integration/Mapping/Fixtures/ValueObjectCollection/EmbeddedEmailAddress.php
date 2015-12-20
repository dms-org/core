<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObjectCollection;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\ValueObject;

/**
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EmbeddedEmailAddress extends ValueObject
{
    /**
     * @var string
     */
    public $email;

    /**
     * EmbeddedEmailAddress constructor.
     *
     * @param string $email
     */
    public function __construct($email)
    {
        parent::__construct();
        $this->email = $email;
    }

    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function define(ClassDefinition $class)
    {
        $class->property($this->email)->asString();
    }
}
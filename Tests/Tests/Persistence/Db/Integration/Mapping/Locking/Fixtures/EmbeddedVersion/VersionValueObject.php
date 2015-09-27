<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Locking\Fixtures\EmbeddedVersion;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\ValueObject;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class VersionValueObject extends ValueObject
{
    /**
     * @var int|null
     */
    public $number;

    /**
     * @inheritDoc
     */
    public function __construct($number = null)
    {
        parent::__construct();
        $this->number = $number;
    }

    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function define(ClassDefinition $class)
    {
        $class->property($this->number)->nullable()->asInt();
    }
}
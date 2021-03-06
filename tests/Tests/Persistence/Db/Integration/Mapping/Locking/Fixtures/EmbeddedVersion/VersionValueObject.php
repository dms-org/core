<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Locking\Fixtures\EmbeddedVersion;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\ValueObject;

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
    public function __construct(int $number = null)
    {
        parent::__construct();
        if ($number !== null) {
            $this->number = $number;
        }
    }

    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function define(ClassDefinition $class)
    {
        $class->property($this->number)->asInt();
    }
}
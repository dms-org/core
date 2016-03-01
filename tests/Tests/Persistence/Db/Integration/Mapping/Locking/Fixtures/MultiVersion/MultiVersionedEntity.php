<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Locking\Fixtures\MultiVersion;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\Entity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MultiVersionedEntity extends Entity
{
    /**
     * @var int|null
     */
    public $intVersion;

    /**
     * @var \DateTimeImmutable|null
     */
    public $dateVersion;

    /**
     * @inheritDoc
     */
    public function __construct($id = null, $intVersion = null, \DateTimeImmutable $dateVersion = null)
    {
        parent::__construct($id);

        if ($intVersion !== null) {
            $this->intVersion = $intVersion;
        }

        if ($dateVersion !== null) {
            $this->dateVersion = $dateVersion;
        }
    }

    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        $class->property($this->intVersion)->asInt();
        $class->property($this->dateVersion)->asObject(\DateTimeImmutable::class);
    }
}
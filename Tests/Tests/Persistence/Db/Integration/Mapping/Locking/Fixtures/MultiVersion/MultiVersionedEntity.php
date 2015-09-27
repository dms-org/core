<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Locking\Fixtures\MultiVersion;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\Entity;

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
        $this->intVersion  = $intVersion;
        $this->dateVersion = $dateVersion;
    }

    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        $class->property($this->intVersion)->nullable()->asInt();
        $class->property($this->dateVersion)->nullable()->asObject(\DateTimeImmutable::class);
    }
}
<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\DateTimeValueObject;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\Entity;
use Iddigital\Cms\Core\Model\Object\Type\DateTime;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EntityWithDateTime extends Entity
{
    /**
     * @var DateTime
     */
    public $datetime;

    /**
     * @inheritDoc
     */
    public function __construct($id = null, DateTime $datetime)
    {
        parent::__construct($id);
        $this->datetime = $datetime;
    }


    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        $class->property($this->datetime)->asObject(DateTime::class);
    }
}
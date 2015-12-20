<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObjectCollection;

use Dms\Core\Model\IValueObjectCollection;
use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\Entity;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Model\ValueObjectCollection;


/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EntityWithEmails extends Entity
{
    /**
     * @var IValueObjectCollection|EmbeddedEmailAddress[]
     */
    public $emails;

    /**
     * @inheritDoc
     */
    public function __construct($id = null, array $emails = [])
    {
        parent::__construct($id);

        $this->emails = new ValueObjectCollection(EmbeddedEmailAddress::class, $emails);
    }

    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        $class->property($this->emails)->asCollectionOf(Type::object(EmbeddedEmailAddress::class));
    }
}
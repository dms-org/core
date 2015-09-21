<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObjectCollection;

use Iddigital\Cms\Core\Model\IValueObjectCollection;
use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\Entity;
use Iddigital\Cms\Core\Model\Type\Builder\Type;
use Iddigital\Cms\Core\Model\ValueObjectCollection;


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
    public function __construct($id = null)
    {
        parent::__construct($id);

        $this->emails = new ValueObjectCollection(EmbeddedEmailAddress::class);
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
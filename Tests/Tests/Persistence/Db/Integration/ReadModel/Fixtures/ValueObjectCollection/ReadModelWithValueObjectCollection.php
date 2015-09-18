<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\ReadModel\Fixtures\ValueObjectCollection;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\ReadModel;
use Iddigital\Cms\Core\Model\Type\Builder\Type;
use Iddigital\Cms\Core\Model\ValueObjectCollection;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\ValueObjectCollection\EmbeddedEmailAddress;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ReadModelWithValueObjectCollection extends ReadModel
{
    /**
     * @var ValueObjectCollection|EmbeddedEmailAddress[]
     */
    public $emails;

    /**
     * ReadModelWithValueObject constructor.
     *
     * @param EmbeddedEmailAddress[] $emails
     */
    public function __construct(array $emails)
    {
        parent::__construct();
        $this->emails = new ValueObjectCollection(EmbeddedEmailAddress::class, $emails);
    }

    /**
     * @inheritDoc
     */
    protected function define(ClassDefinition $class)
    {
        $class->property($this->emails)->asCollectionOf(Type::object(EmbeddedEmailAddress::class));
    }
}
<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel\Fixtures\ValueObjectCollection;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\ReadModel;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Model\ValueObjectCollection;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObjectCollection\EmbeddedEmailAddress;

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
        $this->emails = EmbeddedEmailAddress::collection($emails);
    }

    /**
     * @inheritDoc
     */
    protected function define(ClassDefinition $class)
    {
        $class->property($this->emails)->asType(EmbeddedEmailAddress::collectionType());
    }
}
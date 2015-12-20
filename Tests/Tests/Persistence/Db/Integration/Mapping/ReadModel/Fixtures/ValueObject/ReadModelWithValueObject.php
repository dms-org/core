<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel\Fixtures\ValueObject;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\ReadModel;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObject\EmbeddedMoneyObject;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ReadModelWithValueObject extends ReadModel
{
    /**
     * @var EmbeddedMoneyObject
     */
    public $money;

    /**
     * ReadModelWithValueObject constructor.
     *
     * @param EmbeddedMoneyObject $money
     */
    public function __construct(EmbeddedMoneyObject $money)
    {
        parent::__construct();
        $this->money = $money;
    }

    /**
     * @inheritDoc
     */
    protected function define(ClassDefinition $class)
    {
        $class->property($this->money)->asObject(EmbeddedMoneyObject::class);
    }
}
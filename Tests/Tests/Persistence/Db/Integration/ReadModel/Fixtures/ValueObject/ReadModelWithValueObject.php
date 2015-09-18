<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\ReadModel\Fixtures\ValueObject;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\ReadModel;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\ValueObject\EmbeddedMoneyObject;

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
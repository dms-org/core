<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObject\Polymorphic;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\ValueObject;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObject\CurrencyEnum;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObject\EmbeddedMoneyObject;


/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EmbeddedMoneyObjectSubClass extends EmbeddedMoneyObject
{
    /**
     * @var string
     */
    public $extra;

    /**
     * @inheritDoc
     */
    public function __construct($cents, CurrencyEnum $currency, $extra)
    {
        parent::__construct($cents, $currency);

        $this->extra = $extra;
    }

    /**
     * @inheritDoc
     */
    protected function define(ClassDefinition $class)
    {
        parent::define($class);

        $class->property($this->extra)->asString();
    }


}
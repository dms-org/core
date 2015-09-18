<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\ValueObject\Polymorphic;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\ValueObject;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\ValueObject\CurrencyEnum;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\ValueObject\EmbeddedMoneyObject;


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
<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\MutableValueObject;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\MutableValueObject;


/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EmbeddedMoneyObject extends MutableValueObject
{
    /**
     * @var int
     */
    public $cents;

    /**
     * @var CurrencyEnum
     */
    public $currency;

    /**
     * EmbeddedMoneyObject constructor.
     *
     * @param int          $cents
     * @param CurrencyEnum $currency
     */
    public function __construct($cents, CurrencyEnum $currency)
    {
        parent::__construct();
        $this->cents    = $cents;
        $this->currency = $currency;
    }

    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function define(ClassDefinition $class)
    {
        $class->property($this->cents)->asInt();
        $class->property($this->currency)->asObject(CurrencyEnum::class);
    }
}
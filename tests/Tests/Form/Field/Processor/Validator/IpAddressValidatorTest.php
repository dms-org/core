<?php

namespace Dms\Core\Tests\Form\Field\Processor\Validator;

use Dms\Core\Form\Field\Processor\FieldValidator;
use Dms\Core\Form\Field\Processor\Validator\EmailValidator;
use Dms\Core\Form\Field\Processor\Validator\IpAddressValidator;
use Dms\Core\Language\Message;
use Dms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class IpAddressValidatorTest extends FieldValidatorTest
{
    /**
     * @return FieldValidator
     */
    protected function validator()
    {
        return new IpAddressValidator($this->processedType());
    }

    /**
     * @inheritDoc
     */
    protected function processedType()
    {
        return Type::string()->nullable();
    }

    /**
     * @return array[]
     */
    public function successTests()
    {
        return [
            [null],
            ['123.123.123.123'],
            ['255.255.255.255'],
            ['2001:cdba:0000:0000:0000:0000:3257:9652'],
        ];
    }

    /**
     * @return array[]
     */
    public function failTests()
    {
        return [
            ['', new Message(IpAddressValidator::MESSAGE)],
            ['134', new Message(IpAddressValidator::MESSAGE)],
            ['4325.5435345', new Message(IpAddressValidator::MESSAGE)],
            ['2001:cdba:0000:000', new Message(IpAddressValidator::MESSAGE)],
        ];
    }
}
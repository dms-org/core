<?php

namespace Dms\Core\Tests\Form\Field\Processor\Validator;

use Dms\Core\Form\Field\Processor\FieldValidator;
use Dms\Core\Form\Field\Processor\Validator\UrlValidator;
use Dms\Core\Language\Message;
use Dms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class UrlValidatorTest extends FieldValidatorTest
{
    /**
     * @return FieldValidator
     */
    protected function validator()
    {
        return new UrlValidator($this->processedType());
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
            ['http://www.google.com'],
            ['https://www.google.com'],
            ['http://iddigital.com.au'],
        ];
    }

    /**
     * @return array[]
     */
    public function failTests()
    {
        return [
            ['', new Message(UrlValidator::MESSAGE)],
            ['abcde', new Message(UrlValidator::MESSAGE)],
            ['abcde.com', new Message(UrlValidator::MESSAGE)],
            ['www.abcde.com', new Message(UrlValidator::MESSAGE)],
            ['test@!!!', new Message(UrlValidator::MESSAGE)],
            ['hi@bye.com@foo', new Message(UrlValidator::MESSAGE)],
            ['124', new Message(UrlValidator::MESSAGE)],
        ];
    }
}
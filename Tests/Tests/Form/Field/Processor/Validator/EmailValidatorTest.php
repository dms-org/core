<?php

namespace Iddigital\Cms\Core\Tests\Form\Field\Processor\Validator;

use Iddigital\Cms\Core\Form\Field\Processor\FieldValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\EmailValidator;
use Iddigital\Cms\Core\Language\Message;
use Iddigital\Cms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EmailValidatorTest extends FieldValidatorTest
{
    /**
     * @return FieldValidator
     */
    protected function validator()
    {
        return new EmailValidator($this->processedType());
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
            ['test@test.com'],
            ['elliot@iddigital.com.au'],
            ['awesome@google.com'],
        ];
    }

    /**
     * @return array[]
     */
    public function failTests()
    {
        return [
            ['', new Message(EmailValidator::MESSAGE)],
            ['abcde', new Message(EmailValidator::MESSAGE)],
            ['test@!!!', new Message(EmailValidator::MESSAGE)],
            ['hi@bye.com@foo', new Message(EmailValidator::MESSAGE)],
            ['124', new Message(EmailValidator::MESSAGE)],
        ];
    }
}
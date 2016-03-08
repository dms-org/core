<?php

namespace Dms\Core\Tests\Form\Field\Processor\Validator;

use Dms\Core\Form\Field\Processor\FieldValidator;
use Dms\Core\Form\Field\Processor\Validator\BoolValidator;
use Dms\Core\Form\Field\Processor\Validator\StringCharactersValidator;
use Dms\Core\Language\Message;
use Dms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class StringCharactersValidatorTest extends FieldValidatorTest
{
    /**
     * @return FieldValidator
     */
    protected function validator()
    {
        return new StringCharactersValidator($this->processedType(), [
            'a' => 'z',
            '0' => '9',
        ]);
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
            [''],
            ['abc'],
            ['123'],
            ['abc123'],
            [implode('', array_merge(range('a', 'z'), range('0', '9')))],
        ];
    }

    /**
     * @return array[]
     */
    public function failTests()
    {
        return [
            ['A', new Message(StringCharactersValidator::MESSAGE, ['valid_chars' => 'a-z, 0-9'])],
            ['/', new Message(StringCharactersValidator::MESSAGE, ['valid_chars' => 'a-z, 0-9'])],
            ['\\', new Message(StringCharactersValidator::MESSAGE, ['valid_chars' => 'a-z, 0-9'])],
            ['abc^123', new Message(StringCharactersValidator::MESSAGE, ['valid_chars' => 'a-z, 0-9'])],
        ];
    }
}
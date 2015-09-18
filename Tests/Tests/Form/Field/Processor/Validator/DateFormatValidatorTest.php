<?php

namespace Iddigital\Cms\Core\Tests\Form\Field\Processor\Validator;

use Iddigital\Cms\Core\Form\Field\Processor\FieldValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\DateFormatValidator;
use Iddigital\Cms\Core\Language\Message;
use Iddigital\Cms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DateFormatValidatorTest extends FieldValidatorTest
{
    /**
     * @return FieldValidator
     */
    protected function validator()
    {
        return new DateFormatValidator($this->processedType(), 'Y-m-d');
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
            ['2000-01-01'],
            ['2011-11-11'],
            ['9999-12-31'],
            ['0000-01-01'],
        ];
    }

    /**
     * @return array[]
     */
    public function failTests()
    {
        return [
            ['abd-01-01', new Message(DateFormatValidator::MESSAGE, ['format' => 'Y-m-d'])],
            ['2000-a1-01', new Message(DateFormatValidator::MESSAGE, ['format' => 'Y-m-d'])],
            ['2000-1-!1', new Message(DateFormatValidator::MESSAGE, ['format' => 'Y-m-d'])],
            ['abc', new Message(DateFormatValidator::MESSAGE, ['format' => 'Y-m-d'])],
        ];
    }
}
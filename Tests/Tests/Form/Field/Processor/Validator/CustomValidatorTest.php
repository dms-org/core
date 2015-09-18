<?php

namespace Iddigital\Cms\Core\Tests\Form\Field\Processor\Validator;

use Iddigital\Cms\Core\Form\Field\Processor\FieldValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\CustomValidator;
use Iddigital\Cms\Core\Language\Message;
use Iddigital\Cms\Core\Model\Type\MixedType;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CustomValidatorTest extends FieldValidatorTest
{
    /**
     * @return FieldValidator
     */
    protected function validator()
    {
        return new CustomValidator($this->processedType(), function ($input, array &$messages) {
            return $input % 2 === 0;
        }, 'custom-message', ['custom_params']);
    }

    /**
     * @inheritDoc
     */
    protected function processedType()
    {
        return new MixedType();
    }

    /**
     * @return array[]
     */
    public function successTests()
    {
        return [
            [null],
            [2],
            [4.0],
            [6],
            ['8'],
        ];
    }

    /**
     * @return array[]
     */
    public function failTests()
    {
        return [
            [1, new Message('custom-message', ['custom_params'])],
            [3, new Message('custom-message', ['custom_params'])],
        ];
    }
}
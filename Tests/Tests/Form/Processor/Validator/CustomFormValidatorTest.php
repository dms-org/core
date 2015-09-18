<?php

namespace Iddigital\Cms\Core\Tests\Form\Processor\Validator;

use Iddigital\Cms\Core\Form\Processor\FormValidator;
use Iddigital\Cms\Core\Form\Processor\Validator\CustomFormValidator;
use Iddigital\Cms\Core\Language\Message;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CustomFormValidatorTest extends FormValidatorTest
{

    /**
     * @return FormValidator
     */
    protected function validator()
    {
        return new CustomFormValidator(function (array $input) {
            return isset($input['required']);
        }, 'validation.custom');
    }

    /**
     * @return array[]
     */
    public function successTests()
    {
        return [
                [['required' => '']],
                [['foo' => 'bar', 'required' => '']],
                [['required' => 1]],
                [['required' => false]],
        ];
    }

    /**
     * @return array[]
     */
    public function failTests()
    {
        return [
                [[], new Message('validation.custom')],
                [['foo' => 'bar'], new Message('validation.custom')],
                [['required' => null], new Message('validation.custom')],
        ];
    }
}
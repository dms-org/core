<?php

namespace Dms\Core\Tests\Form\Processor\Validator;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Form\Processor\FormValidator;
use Dms\Core\Language\Message;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class FormValidatorTest extends CmsTestCase
{
    /**
     * @var FormValidator
     */
    protected $validator;

    protected function setUp()
    {
        $this->validator = $this->validator();
    }

    /**
     * @return FormValidator
     */
    abstract protected function validator();

    /**
     * @return array[]
     */
    abstract public function successTests();

    /**
     * @return array[]
     */
    abstract public function failTests();

    /**
     * @dataProvider successTests
     */
    public function testValidValues(array $input)
    {
        $messages = [];
        $output   = $this->validator->process($input, $messages);

        $this->assertSame($input, $output,
                'A validator should return the same value as the input.');
        $this->assertEmpty($messages, 'A valid input should not generate any messages');
    }

    public function testUnprocessReturnsInputtedValue()
    {
        $output = $this->validator->unprocess(['foo' => 'bar']);

        $this->assertSame(['foo' => 'bar'], $output,
                'A validator should return the same value as the input.');
    }

    /**
     * @dataProvider failTests
     */
    public function testInvalidValues(array $input, $messages)
    {
        if ($messages instanceof Message) {
            $messages = [$messages];
        }

        $actualMessages = [];
        $output         = $this->validator->process($input, $actualMessages);

        $this->assertSame($input, $output,
                'A validator should return the same value as the input.');
        $this->assertNotEmpty($actualMessages, 'An invalid input should generate messages');
        $this->assertEquals($messages, $actualMessages);
    }
}
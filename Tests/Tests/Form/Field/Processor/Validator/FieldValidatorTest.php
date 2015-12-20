<?php

namespace Dms\Core\Tests\Form\Field\Processor\Validator;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Form\Field\Processor\FieldValidator;
use Dms\Core\Language\Message;
use Dms\Core\Model\Type\IType;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class FieldValidatorTest extends CmsTestCase
{

    /**
     * @var FieldValidator
     */
    protected $validator;

    protected function setUp()
    {
        $this->validator = $this->validator();
    }

    /**
     * @return FieldValidator
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
     * @return IType
     */
    abstract protected function processedType();

    public function testProcessedType()
    {
        $this->assertEquals($this->processedType(), $this->validator->getProcessedType());
    }

    /**
     * @dataProvider successTests
     */
    public function testValidValues($input)
    {
        $messages = [];
        $output   = $this->validator->process($input, $messages);

        $this->assertSame($input, $output,
            'A validator should return the same value as the input.');
        $this->assertEmpty($messages, 'A valid input should not generate any messages');
    }

    public function testUnprocessReturnsInputtedValue()
    {
        $output = $this->validator->unprocess('abc123');

        $this->assertSame('abc123', $output,
            'A validator should return the same value as the input.');
    }

    /**
     * @dataProvider failTests
     */
    public function testInvalidValues($input, $messages)
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
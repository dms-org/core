<?php

namespace Dms\Core\Tests\Form\Field\Processor;

use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Form\Field\Processor\ArrayAllProcessor;
use Dms\Core\Form\Field\Processor\CustomProcessor;
use Dms\Core\Form\Field\Processor\Validator\IntValidator;
use Dms\Core\Form\IFieldProcessor;
use Dms\Core\Language\Message;
use Dms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ArrayAllProcessorTest extends FieldProcessorTest
{
    /**
     * @return IFieldProcessor
     */
    protected function processor()
    {
        return new ArrayAllProcessor(
            Field::element()->string()->process(
                new CustomProcessor(Type::string(), function ($value) {
                    return "Hi {$value}!";
                }, function ($value) {
                    return substr($value, 3, -1);
                })
            )->build()
        );
    }

    /**
     * @inheritDoc
     */
    protected function processedType()
    {
        return Type::arrayOf(Type::string())->nullable();
    }

    /**
     * @return array[]
     */
    public function processTests()
    {
        return [
            [null, null],
            [['Joe'], ['Hi Joe!']],
            [['foo' => 'Joe'], ['foo' => 'Hi Joe!']],
            [['Joe', 'Mary', 'Carol'], ['Hi Joe!', 'Hi Mary!', 'Hi Carol!']],
        ];
    }

    /**
     * @return array[]
     */
    public function unprocessTests()
    {
        return [
            [null, null],
            [['Hi Joe!'], ['Joe']],
            [['foo' => 'Hi Joe!'], ['foo' => 'Joe']],
            [['Hi Joe!', 'Hi Mary!', 'Hi Carol!'], ['Joe', 'Mary', 'Carol']],
        ];
    }

    public function testReturnsElementKeyInValidationMessages()
    {
        $processor = new ArrayAllProcessor(Field::name('field')->label('Field')->int()->build());

        $messages = [];
        $processor->process([0 => '1', 1 => '2', 2 => 'b'], $messages);

        $this->assertEquals([new Message(IntValidator::MESSAGE, [
            'key'   => 2,
            'field' => 'Field',
            'input' => 'b',
        ])], $messages);
    }
}
<?php

namespace Iddigital\Cms\Core\Tests\Form\Field\Processor;

use Iddigital\Cms\Core\Form\Field\Processor\TypeProcessor;
use Iddigital\Cms\Core\Form\IFieldProcessor;
use Iddigital\Cms\Core\Language\Message;
use Iddigital\Cms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TypeProcessorTest extends FieldProcessorTest
{
    /**
     * @return IFieldProcessor
     */
    protected function processor()
    {
        return new TypeProcessor('string');
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
    public function processTests()
    {
        return [
                [null, null],
                ['abc', 'abc'],
                [123, '123'],
                [-1.2, '-1.2'],
                [true, '1'],
        ];
    }

    /**
     * @return array[]
     */
    public function unprocessTests()
    {
        return [
                [null, null],
                [' ', ' '],
                ['---', '---'],
                [123, 123],
        ];
    }

    public function testIntTypeProcessor()
    {
        $messages  = [];
        $processor = new TypeProcessor('int');

        $this->assertSame(17, $processor->process('17.0', $messages));
    }

    public function testInvalidTypes()
    {
        $messages  = [];
        $processor = new TypeProcessor('string');

        $processor->process([1, 2, 3], $messages);

        $this->assertEquals([new Message(TypeProcessor::MESSAGE, ['expected_type' => 'string', 'actual_type' => 'array'])], $messages);

        $messages  = [];
        $processor->process($this, $messages);

        $this->assertEquals([new Message(TypeProcessor::MESSAGE, ['expected_type' => 'string', 'actual_type' => 'object'])], $messages);
    }
}
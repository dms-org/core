<?php

namespace Dms\Core\Tests\Form\Field\Processor;

use Dms\Core\Form\Field\Processor\EmptyStringToNullProcessor;
use Dms\Core\Form\IFieldProcessor;
use Dms\Core\Form\Field\Processor\BoolProcessor;
use Dms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EmptyStringToNullProcessorTest extends FieldProcessorTest
{
    /**
     * @return IFieldProcessor
     */
    protected function processor()
    {
        return new EmptyStringToNullProcessor();
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
            ['1', '1'],
            ['abc', 'abc'],
            [' ', ' '],
            ['', null],
        ];
    }

    /**
     * @return array[]
     */
    public function unprocessTests()
    {
        return [
            [null, null],
            ['1', '1'],
            ['abc', 'abc'],
            [' ', ' '],
        ];
    }
}
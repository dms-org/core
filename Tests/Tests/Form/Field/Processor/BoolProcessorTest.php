<?php

namespace Iddigital\Cms\Core\Tests\Form\Field\Processor;

use Iddigital\Cms\Core\Form\IFieldProcessor;
use Iddigital\Cms\Core\Form\Field\Processor\BoolProcessor;
use Iddigital\Cms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class BoolProcessorTest extends FieldProcessorTest
{
    /**
     * @return IFieldProcessor
     */
    protected function processor()
    {
        return new BoolProcessor();
    }

    /**
     * @inheritDoc
     */
    protected function processedType()
    {
        return Type::bool()->nullable();
    }

    /**
     * @return array[]
     */
    public function processTests()
    {
        return [
            [null, null],
            [true, true],
            [false, false],
            [1, true],
            [0, false],
            [1.0, true],
            [0.0, false],
            ['1', true],
            ['0', false],
            ['yes', true],
            ['no', false],
        ];
    }

    /**
     * @return array[]
     */
    public function unprocessTests()
    {
        return [
            [null, null],
            [true, true],
            [false, false],
            [1, 1],
            [0, 0],
        ];
    }
}
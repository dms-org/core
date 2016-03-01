<?php

namespace Dms\Core\Tests\Form\Field\Processor;

use Dms\Core\Form\Field\Processor\ArrayFillWithNullsProcessor;
use Dms\Core\Form\IFieldProcessor;
use Dms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ArrayFillWithNullsProcessorTest extends FieldProcessorTest
{
    /**
     * @return IFieldProcessor
     */
    protected function processor()
    {
        return new ArrayFillWithNullsProcessor(
            Type::arrayOf(Type::mixed()),
            [0, 1, 5]
        );
    }

    /**
     * @inheritDoc
     */
    protected function processedType()
    {
        return Type::arrayOf(Type::mixed())->nullable();
    }

    /**
     * @return array[]
     */
    public function processTests()
    {
        return [
            [null, null],
            [[], [0 => null, 1 => null, 5 => null]],
            [['Joe'], ['Joe', 1 => null, 5 => null]],
            [['foo' => 'Joe'], ['foo' => 'Joe', 0 => null, 1 => null, 5 => null]],
        ];
    }

    /**
     * @return array[]
     */
    public function unprocessTests()
    {
        return [
            [null, null],
            [['Joe'], ['Joe']],
            [['foo' => 'Joe'], ['foo' => 'Joe']],
            [['Hi Joe!', 'Hi Mary!', 'Hi Carol!'], ['Hi Joe!', 'Hi Mary!', 'Hi Carol!']],
        ];
    }
}
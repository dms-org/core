<?php

namespace Dms\Core\Tests\Form\Field\Processor;

use Dms\Core\Form\Field\Processor\TrimProcessor;
use Dms\Core\Form\IFieldProcessor;
use Dms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TrimProcessorTest extends FieldProcessorTest
{
    /**
     * @return IFieldProcessor
     */
    protected function processor()
    {
        return new TrimProcessor(' -_');
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
                [' ', ''],
                ['-', ''],
                ['_', ''],
                ['    ---__--- ', ''],
                ['abc', 'abc'],
                ['__COOL___', 'COOL'],
                ['__C-O O-L___', 'C-O O-L'],
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
        ];
    }
}
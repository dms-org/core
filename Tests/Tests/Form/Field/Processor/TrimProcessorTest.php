<?php

namespace Iddigital\Cms\Core\Tests\Form\Field\Processor;

use Iddigital\Cms\Core\Form\Field\Processor\TrimProcessor;
use Iddigital\Cms\Core\Form\IFieldProcessor;
use Iddigital\Cms\Core\Model\Type\Builder\Type;

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
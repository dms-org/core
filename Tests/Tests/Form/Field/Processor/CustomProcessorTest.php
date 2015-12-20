<?php

namespace Dms\Core\Tests\Form\Field\Processor;

use Dms\Core\Form\IFieldProcessor;
use Dms\Core\Form\Field\Processor\CustomProcessor;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Model\Type\IType;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CustomProcessorTest extends FieldProcessorTest
{
    /**
     * @return IFieldProcessor
     */
    protected function processor()
    {
        return new CustomProcessor(Type::string(), function ($input, array &$message) {
            return $input . 'foo';
        }, function ($input) {
            return substr($input, 0, -3);
        });
    }

    /**
     * @inheritDoc
     */
    protected function processedType()
    {
        return Type::string();
    }

    /**
     * @return array[]
     */
    public function processTests()
    {
        return [
            [null, null],
            ['', 'foo'],
            [0, '0foo'],
            ['foo', 'foofoo'],
        ];
    }

    /**
     * @return array[]
     */
    public function unprocessTests()
    {
        return [
            [null, null],
            ['foo', ''],
            ['0foo', '0'],
            ['foofoo', 'foo'],
        ];
    }
}
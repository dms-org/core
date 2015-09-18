<?php

namespace Iddigital\Cms\Core\Tests\Form\Field\Processor;

use Iddigital\Cms\Core\Form\IFieldProcessor;
use Iddigital\Cms\Core\Form\Field\Processor\CustomProcessor;
use Iddigital\Cms\Core\Model\Type\Builder\Type;
use Iddigital\Cms\Core\Model\Type\IType;

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
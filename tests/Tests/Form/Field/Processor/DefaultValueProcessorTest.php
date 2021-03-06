<?php

namespace Dms\Core\Tests\Form\Field\Processor;

use Dms\Core\Form\IFieldProcessor;
use Dms\Core\Form\Field\Processor\DefaultValueProcessor;
use Dms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DefaultValueProcessorTest extends FieldProcessorTest
{
    /**
     * @return IFieldProcessor
     */
    protected function processor()
    {
        return new DefaultValueProcessor(Type::mixed(), '123');
    }

    /**
     * @inheritDoc
     */
    protected function processedType()
    {
        return Type::mixed()->nonNullable()->union(Type::string());
    }

    /**
     * @return array[]
     */
    public function processTests()
    {
        return [
            [null, '123'],
            [true, true],
            [false, false],
            ['', ''],
            [0, 0],
            ['dsfsdf', 'dsfsdf'],
            [$t = new \DateTime(), $t],
        ];
    }

    /**
     * @return array[]
     */
    public function unprocessTests()
    {
        return [
            ['123', '123'],
            [false, false],
            ['', ''],
            [0, 0],
            ['dsfsdf', 'dsfsdf'],
            [$t = new \DateTime(), $t],
        ];
    }
}
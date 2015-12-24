<?php

namespace Dms\Core\Tests\Form\Field\Processor;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Form\Field\Processor\EnumProcessor;
use Dms\Core\Form\IFieldProcessor;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Tests\Form\Field\Processor\Fixtures\StatusEnum;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EnumProcessorTest extends FieldProcessorTest
{
    /**
     * @return IFieldProcessor
     */
    protected function processor()
    {
        return new EnumProcessor(StatusEnum::class);
    }

    /**
     * @inheritDoc
     */
    protected function processedType()
    {
        return Type::object(StatusEnum::class)->nullable();
    }

    /**
     * @return array[]
     */
    public function processTests()
    {
        return [
                [null, null],
                ['inactive', new StatusEnum(StatusEnum::INACTIVE)],
                ['active', new StatusEnum(StatusEnum::ACTIVE)],
        ];
    }

    /**
     * @return array[]
     */
    public function unprocessTests()
    {
        return [
                [null, null],
                [new StatusEnum(StatusEnum::INACTIVE), 'inactive'],
                [new StatusEnum(StatusEnum::ACTIVE), 'active'],
        ];
    }

    public function testInvalidEnumClass()
    {
        $this->setExpectedException(InvalidArgumentException::class);

        new EnumProcessor(\DateTime::class);
    }
}
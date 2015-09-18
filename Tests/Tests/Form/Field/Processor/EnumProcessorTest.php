<?php

namespace Iddigital\Cms\Core\Tests\Form\Field\Processor;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Form\Field\Processor\EnumProcessor;
use Iddigital\Cms\Core\Form\IFieldProcessor;
use Iddigital\Cms\Core\Model\Type\Builder\Type;
use Iddigital\Cms\Core\Tests\Form\Field\Processor\Fixtures\StatusEnum;

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
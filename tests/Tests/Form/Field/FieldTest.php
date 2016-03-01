<?php

namespace Dms\Core\Tests\Form\Field;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Form\Field\Processor\DefaultValueProcessor;
use Dms\Core\Form\Field\Processor\TypeProcessor;
use Dms\Core\Form\Field\Processor\Validator\NotSuppliedValidator;
use Dms\Core\Form\Field\Type\FieldType;
use Dms\Core\Form\InvalidInputException;
use Dms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FieldTest extends CmsTestCase
{
    public function testWithInitialValue()
    {
        $testField = Field::name('foo')->label('Foo')->string()->build();

        $this->assertSame(null, $testField->getInitialValue());
        $this->assertSame(null, $testField->getType()->get(FieldType::ATTR_INITIAL_VALUE));

        $newField = $testField->withInitialValue('abc');

        $this->assertNotEquals($testField, $newField);
        $this->assertSame('abc', $newField->getInitialValue());
        $this->assertSame('abc', $newField->getType()->get(FieldType::ATTR_INITIAL_VALUE));
        $this->assertSame(null, $newField->withInitialValue(null)->getInitialValue());
        $this->assertSame(null, $newField->withInitialValue(null)->getType()->get(FieldType::ATTR_INITIAL_VALUE));
    }

    public function testReadOnlyField()
    {
        $testField = Field::name('foo')->label('Foo')
                ->string()
                ->value('abc')
                ->readonly()
                ->build();

        $this->assertSame('abc', $testField->getInitialValue());
        $this->assertSame(true, $testField->getType()->get(FieldType::ATTR_READ_ONLY));

        $this->assertEquals([
            new NotSuppliedValidator(Type::mixed()),
            new DefaultValueProcessor(Type::string()->nullable(), 'abc'),
            new TypeProcessor('string')
        ], $testField->getType()->getProcessors());

        $this->assertSame('abc', $testField->process(null));

        $this->assertThrows(function () use ($testField) {
            $testField->process('aaa');
        }, InvalidInputException::class);
    }
}
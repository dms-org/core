<?php

namespace Dms\Core\Tests\Form\Field;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Form\Field\Processor\CustomProcessor;
use Dms\Core\Form\Field\Processor\DefaultValueProcessor;
use Dms\Core\Form\Field\Processor\OverrideValueProcessor;
use Dms\Core\Form\Field\Processor\TypeProcessor;
use Dms\Core\Form\Field\Type\FieldType;
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

    public function testWithCustomerProcessors()
    {
        $testField = Field::name('foo')->label('Foo')
            ->string()
            ->value('abc')
            ->process(
                $processor = new CustomProcessor(
                    Type::string(),
                    function ($i) {
                        return $i . '!!';
                    },
                    function ($i) {
                        return substr($i, 0, -2);
                    }
                )
            )
            ->build();

        $this->assertSame([$processor], $testField->getCustomProcessors());
        $this->assertSame('abc!!', $testField->getInitialValue());

        $newField = $testField->withCustomProcessors([
            $newProcessor = new CustomProcessor(
                Type::string(),
                function ($i) {
                    return $i . '!!!';
                },
                function ($i) {
                    return substr($i, 0, -3);
                }
            )
        ]);

        $this->assertSame([$processor], $testField->getCustomProcessors());
        $this->assertSame('abc!!', $testField->getInitialValue());
        $this->assertSame([$newProcessor], $newField->getCustomProcessors());
        $this->assertSame(null, $newField->getInitialValue());
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
            new OverrideValueProcessor(Type::mixed(), 'abc'),
            new TypeProcessor('string'),
            new DefaultValueProcessor(Type::string()->nullable(), 'abc'),
        ], $testField->getType()->getProcessors());

        $this->assertSame('abc', $testField->process(null));
        $this->assertSame('abc', $testField->process('fsdf'));
        $this->assertSame('abc', $testField->process('345543'));
        $this->assertSame('abc', $testField->process('abc'));

        $this->assertSame(null, $testField->withInitialValue(null)->process(null));
        $this->assertSame(null, $testField->withInitialValue(null)->process('abc'));
    }
}
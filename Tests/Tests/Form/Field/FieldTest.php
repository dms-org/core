<?php

namespace Dms\Core\Tests\Form\Field;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Form\Field\Type\FieldType;

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
}
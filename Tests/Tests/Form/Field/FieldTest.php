<?php

namespace Iddigital\Cms\Core\Tests\Form\Field;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Form\Field\Builder\Field;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FieldTest extends CmsTestCase
{
    public function testWithInitialValue()
    {
        $testField = Field::name('foo')->label('Foo')->string()->build();

        $this->assertSame(null, $testField->getInitialValue());

        $newField = $testField->withInitialValue('abc');

        $this->assertNotEquals($testField, $newField);
        $this->assertSame('abc', $newField->getInitialValue());
        $this->assertSame(null, $newField->withInitialValue(null)->getInitialValue());
    }
}
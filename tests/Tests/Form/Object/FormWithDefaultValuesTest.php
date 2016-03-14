<?php

namespace Dms\Core\Tests\Form\Object;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Tests\Form\Object\Fixtures\FormWithDefaults;
use Dms\Core\Tests\Form\Object\Fixtures\SubFormWithDefaults;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FormWithDefaultValuesTest extends CmsTestCase
{
    public function testGetInitialValues()
    {
        $expectedValues = [
            'terms'      => ['foo', 'bar', 'baz'],
            'event_date' => new \DateTimeImmutable('2015-01-01'),
            'inner'      => new SubFormWithDefaults(),
        ];

        $this->assertEquals($expectedValues, FormWithDefaults::initialValues());
    }

    public function testSerialization()
    {
        $this->assertEquals(new FormWithDefaults(), unserialize(serialize(new FormWithDefaults())));
    }
}
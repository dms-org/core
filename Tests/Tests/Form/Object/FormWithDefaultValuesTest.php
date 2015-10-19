<?php

namespace Iddigital\Cms\Core\Tests\Form\Object;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Tests\Form\Object\Fixtures\FormWithDefaults;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FormWithDefaultValuesTest extends CmsTestCase
{
    public function testGetInitialValues()
    {
        $expectedValues = [
                'terms'         => ['foo', 'bar', 'baz'],
                'event_date'    => '2015-01-01',
                'inner_default' => 0.123,
                'inner_awesome' => 'cool',
        ];

        $this->assertSame($expectedValues, FormWithDefaults::initialValues());
    }
}
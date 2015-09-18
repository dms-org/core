<?php

namespace Iddigital\Cms\Core\Tests\Model\Object;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Model\Object\ImmutablePropertyException;
use Iddigital\Cms\Core\Model\Object\InvalidPropertyValueException;
use Iddigital\Cms\Core\Tests\Model\Object\Fixtures\ImmutableProperty;
use Iddigital\Cms\Core\Tests\Model\Object\Fixtures\NullableProperty;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class NullablePropertyTest extends CmsTestCase
{
    public function testDoesNotThrowExceptionIfSetToInt()
    {
        $object = new NullableProperty();

        $object->prop = 123;
    }

    public function testDoesNotThrowExceptionIfSetToNull()
    {
        $object = new NullableProperty();

        $object->prop = 123;
        $object->prop = null;
    }

    public function testThrowsExceptionIfSetToInvalidType()
    {
        $this->setExpectedException(InvalidPropertyValueException::class);
        $object = new NullableProperty();

        $object->prop = 'string';
    }
}
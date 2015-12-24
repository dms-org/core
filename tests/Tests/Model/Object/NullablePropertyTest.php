<?php

namespace Dms\Core\Tests\Model\Object;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Model\Object\ImmutablePropertyException;
use Dms\Core\Model\Object\InvalidPropertyValueException;
use Dms\Core\Tests\Model\Object\Fixtures\ImmutableProperty;
use Dms\Core\Tests\Model\Object\Fixtures\NullableProperty;

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
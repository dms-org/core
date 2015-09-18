<?php

namespace Iddigital\Cms\Core\Tests\Model\Object;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Model\Object\ImmutablePropertyException;
use Iddigital\Cms\Core\Tests\Model\Object\Fixtures\ImmutableProperty;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ImmutablePropertyTest extends CmsTestCase
{
    public function testDoesNotThrowExceptionIfNotChanged()
    {
        $object = new ImmutableProperty();

        $object->two = 'abc';
        $object->two = 'abc';
        $object->two = 'abc';
    }

    public function testThrowsExceptionIfChanged()
    {
        $object = new ImmutableProperty();

        $object->two = 'abc';

        $this->setExpectedException(ImmutablePropertyException::class);

        $object->two = '123';
    }
}
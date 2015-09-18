<?php

namespace Iddigital\Cms\Core\Tests\Model\Object;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Model\Object\ImmutablePropertyException;
use Iddigital\Cms\Core\Tests\Model\Object\Fixtures\TestValueObject;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ValueObjectsImmutabilityTest extends CmsTestCase
{
    public function testValueObjectsAreImmutableByDefault()
    {
        $object = new TestValueObject();

        $object->one = 'abc';

        $this->setExpectedException(ImmutablePropertyException::class);

        $object->one = '123';
    }
}
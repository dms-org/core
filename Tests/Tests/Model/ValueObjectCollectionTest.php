<?php

namespace Iddigital\Cms\Core\Tests\Model;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Model\IValueObject;
use Iddigital\Cms\Core\Model\TypedCollection;
use Iddigital\Cms\Core\Model\ValueObjectCollection;
use Iddigital\Cms\Core\Tests\Model\Fixtures\SubObject;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ValueObjectCollectionTest extends CmsTestCase
{
    public function testValidType()
    {
        new  ValueObjectCollection(IValueObject::class);;
    }

    public function testInvalidType()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        new  ValueObjectCollection(\stdClass::class);
    }

    public function testProjectionReturnsTypedCollection()
    {
        $valueObjects = SubObject::collection([new SubObject('data')]);
        $props = $valueObjects->select(function (SubObject $object) {
            return $object->prop;
        });

        $this->assertInstanceOf(TypedCollection::class, $props);
        $this->assertNotInstanceOf(ValueObjectCollection::class, $props);

        $this->assertEquals(['data'], $props->asArray());
    }
}
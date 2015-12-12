<?php

namespace Iddigital\Cms\Core\Tests\Model\Object;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Tests\Model\Object\Fixtures\OverriddenPropertyName;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class OverriddenPropertyNameTypedObjectTest extends CmsTestCase
{
    public function testOverriddenProperty()
    {
        $object = new OverriddenPropertyName();

        $this->assertSame(null, $object->getProp());
        $object->setProp(true);
        $this->assertSame(true, $object->getProp());
    }
}
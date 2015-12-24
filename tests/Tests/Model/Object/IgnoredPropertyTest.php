<?php

namespace Dms\Core\Tests\Model\Object;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Tests\Model\Object\Fixtures\IgnoredProperty;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class IgnoredPropertyTest extends CmsTestCase
{
    public function testNotInClassDefinition()
    {
        $this->assertEquals([
                'one'   => Type::mixed(),
                'three' => Type::mixed(),
        ], IgnoredProperty::definition()->getPropertyTypeMap());
    }
}
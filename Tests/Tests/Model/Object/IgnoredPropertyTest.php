<?php

namespace Iddigital\Cms\Core\Tests\Model\Object;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Model\Type\Builder\Type;
use Iddigital\Cms\Core\Tests\Model\Object\Fixtures\IgnoredProperty;

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
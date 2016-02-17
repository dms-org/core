<?php

namespace Dms\Core\Tests\Model\Object;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Tests\Model\Object\Fixtures\TypedObjectWithSpec;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TypedObjectWithSpecificationTest extends CmsTestCase
{
    public function testSpecification()
    {
        $spec = TypedObjectWithSpec::testWherePropEquals(6);

        $this->assertSame(TypedObjectWithSpec::class, $spec->getClass()->getClassName());
        $this->assertSame(true, $spec->isSatisfiedBy(new TypedObjectWithSpec(6)));
        $this->assertSame(false, $spec->isSatisfiedBy(new TypedObjectWithSpec(5)));
        $this->assertSame(false, $spec->isSatisfiedBy(new TypedObjectWithSpec(7)));
    }
}
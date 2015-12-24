<?php

namespace Dms\Core\Tests\Model\Object;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Model\Object\ConflictingPropertyNameException;
use Dms\Core\Tests\Model\Object\Fixtures\ConflictingPropertyName;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ConflictingPropertyNameTypedObjectTest extends CmsTestCase
{
    public function testConflictingProperty()
    {
        $this->setExpectedException(ConflictingPropertyNameException::class);
        new ConflictingPropertyName();
    }
}
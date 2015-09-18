<?php

namespace Iddigital\Cms\Core\Tests\Model\Object;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Model\Object\ConflictingPropertyNameException;
use Iddigital\Cms\Core\Tests\Model\Object\Fixtures\ConflictingPropertyName;

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
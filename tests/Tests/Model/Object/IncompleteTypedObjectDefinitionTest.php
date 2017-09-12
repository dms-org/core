<?php

namespace Dms\Core\Tests\Model\Object;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Model\Object\IncompleteClassDefinitionException;
use Dms\Core\Tests\Model\Object\Fixtures\IncompleteClassDefinition;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class IncompleteTypedObjectDefinitionTest extends CmsTestCase
{
    public function testIncompleteDefinition()
    {
        $this->expectException(IncompleteClassDefinitionException::class);
        IncompleteClassDefinition::build();
    }
}
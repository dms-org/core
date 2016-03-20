<?php

namespace Dms\Core\Tests\Model\Object;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Tests\Model\Object\Fixtures\DuplicatePropertyDefinition;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DuplicatePropertyObjectDefinitionTest extends CmsTestCase
{
    public function testDuplicateDefinitionIsIgnored()
    {
        DuplicatePropertyDefinition::build();
    }
}
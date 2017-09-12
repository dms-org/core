<?php

namespace Dms\Core\Tests\Model\Object;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Model\Object\InvalidPropertyDefinitionException;
use Dms\Core\Tests\Model\Object\Fixtures\InvalidPropertyDefinition;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class InvalidPropertyObjectDefinitionTest extends CmsTestCase
{
    public function testIncompleteDefinition()
    {
        $this->expectException(InvalidPropertyDefinitionException::class);
        InvalidPropertyDefinition::build();
    }
}
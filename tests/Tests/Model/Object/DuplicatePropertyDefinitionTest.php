<?php

namespace Dms\Core\Tests\Model\Object;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Model\Object\DuplicatePropertyDefinitionException;
use Dms\Core\Tests\Model\Object\Fixtures\DuplicatePropertyDefinition;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DuplicatePropertyObjectDefinitionTest extends CmsTestCase
{
    public function testIncompleteDefinition()
    {
        $this->setExpectedException(DuplicatePropertyDefinitionException::class);
        DuplicatePropertyDefinition::build();
    }
}
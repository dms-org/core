<?php

namespace Iddigital\Cms\Core\Tests\Model\Object;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Model\Object\IncompleteClassDefinitionException;
use Iddigital\Cms\Core\Tests\Model\Object\Fixtures\IncompleteClassDefinition;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class IncompleteTypedObjectDefinitionTest extends CmsTestCase
{
    public function testIncompleteDefinition()
    {
        $this->setExpectedException(IncompleteClassDefinitionException::class);
        IncompleteClassDefinition::build();
    }
}
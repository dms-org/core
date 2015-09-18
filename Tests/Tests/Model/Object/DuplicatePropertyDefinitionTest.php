<?php

namespace Iddigital\Cms\Core\Tests\Model\Object;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Model\Object\DuplicatePropertyDefinitionException;
use Iddigital\Cms\Core\Tests\Model\Object\Fixtures\DuplicatePropertyDefinition;

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
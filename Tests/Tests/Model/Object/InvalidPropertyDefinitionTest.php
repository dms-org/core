<?php

namespace Iddigital\Cms\Core\Tests\Model\Object;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Model\Object\InvalidPropertyDefinitionException;
use Iddigital\Cms\Core\Tests\Model\Object\Fixtures\InvalidPropertyDefinition;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class InvalidPropertyObjectDefinitionTest extends CmsTestCase
{
    public function testIncompleteDefinition()
    {
        $this->setExpectedException(InvalidPropertyDefinitionException::class);
        InvalidPropertyDefinition::build();
    }
}
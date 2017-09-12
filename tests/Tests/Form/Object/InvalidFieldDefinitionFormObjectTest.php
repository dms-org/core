<?php

namespace Dms\Core\Tests\Form\Object;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Form\Object\InvalidFieldDefinitionException;
use Dms\Core\Tests\Form\Object\Fixtures\InvalidFieldDefinition;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class InvalidFieldDefinitionFormObjectTest extends CmsTestCase
{
    public function testInvalidFieldDefinition()
    {
        $this->expectException(InvalidFieldDefinitionException::class);
        InvalidFieldDefinition::formDefinition();
    }
}
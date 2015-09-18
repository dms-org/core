<?php

namespace Iddigital\Cms\Core\Tests\Form\Object;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Form\Object\InvalidFieldDefinitionException;
use Iddigital\Cms\Core\Tests\Form\Object\Fixtures\InvalidFieldDefinition;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class InvalidFieldDefinitionFormObjectTest extends CmsTestCase
{
    public function testInvalidFieldDefinition()
    {
        $this->setExpectedException(InvalidFieldDefinitionException::class);
        InvalidFieldDefinition::formDefinition();
    }
}
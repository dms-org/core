<?php

namespace Iddigital\Cms\Core\Tests\Model\Object;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Model\Object\TypedObject;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class TypedObjectTest extends CmsTestCase
{
    /**
     * @var TypedObject
     */
    protected $object;

    /**
     * @return TypedObject
     */
    protected abstract function buildObject();

    protected function setUp()
    {
        $this->object = $this->buildObject();
    }
}
<?php

namespace Dms\Core\Tests\Model\Object;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Model\Object\TypedObject;

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

    protected function setUp(): void
    {
        $this->object = $this->buildObject();
    }
}
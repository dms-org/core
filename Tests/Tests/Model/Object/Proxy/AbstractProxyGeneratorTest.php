<?php

namespace Dms\Core\Tests\Model\Object\Proxy;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Model\Object\AbstractProxyGenerator;
use Dms\Core\Tests\Model\Object\Proxy\Fixtures\AbstractMethods;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class AbstractProxyGeneratorTest extends CmsTestCase
{
    public function testProxyGenerator()
    {
        $instance = AbstractProxyGenerator::createProxyInstance(AbstractMethods::class);
        $this->assertInstanceOf(AbstractMethods::class, $instance);
    }

    public function testProxyGeneratorNonAbstractClass()
    {
        $instance = AbstractProxyGenerator::createProxyInstance(\stdClass::class);
        $this->assertInstanceOf(\stdClass::class, $instance);
    }
}